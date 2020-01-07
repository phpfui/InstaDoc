<?php

namespace PHPFUI\InstaDoc;

class FileManager
	{
	private const CLASSES = '.Classes';
	private const GIT = '.Git';
	private const NAMESPACE = '.Namespace';

	private const REPO_ROOT = '.Root';
	private const ROOT_NAMESPACE = '\\';
	private $composerJsonPath = '';
	private $excludedNamespaces = [];
	private $includedNamespaces = [];
	private $namespaces = [];

	private $version = 1;

	/**
	 * Make a FileManager.  Pass a composer JSON path to use
	 * Composer dependancies.  This is the path to your
	 * composer.json and composer.lock files. If you don't pass
	 * anything, InstaDoc will not search and display classes loaded
	 * via Composer
	 */
	public function __construct(string $composerJsonPath = '')
		{
		$this->composerJsonPath = str_replace('\\', '/', $composerJsonPath);
		}

	public function getComposerPath() : string
		{
		return $this->composerJsonPath;
		}

	/**
	 * You can add a Namespace directly.  Specify the namespace (no
	 * leading \) and the directory containing the class files.
	 * This is realitive to the current script directory.  You can
	 * also pass an option localGit flag indicating this directory
	 * is in the project git repo.  This will allow you to see the
	 * git history on the file.
	 */
	public function addNamespace(string $namespace, string $directory, bool $localGit = false) : FileManager
		{
		$this->includedNamespaces[$namespace][FileManager::REPO_ROOT] = $directory;
		$this->includedNamespaces[$namespace][] = $directory;
		$this->includedNamespaces[$namespace][FileManager::GIT] = $localGit;
		$this->includedNamespaces[$namespace][FileManager::CLASSES] = [];

		return $this;
		}

	/**
	 * Remove just one namespace from your docs.
	 */
	public function excludeNamespace(string $namespace) : FileManager
		{
		return $this->excludedNamespaces([$namespace]);
		}

	/**
	 * Sometimes you don't feel like a nut. Pass namespaces in an
	 * array to remove them from your documentation.
	 */
	public function excludeNamespaces(array $namespaces) : FileManager
		{
		$this->excludedNamespaces = $this->excludedNamespaces + $namespaces;

		return $this;
		}

	/**
	 * Returns all directories in a namespace index by namespace.
	 *
	 * Array contains all directories in the namespace in an integer
	 * index array plus the following indexes:
	 *
	 * .Root containing the namespace root directory
	 * .Git true if in the local git repo
	 */
	public function getAllNamespaceDirectories(bool $rescan = true) : array
		{
		if (! $rescan)
			{
			return $this->namespaces;
			}

		$directories = [];

		if ($this->composerJsonPath)
			{
			$directories = $this->getAllVendorDirectories($this->composerJsonPath);
			}
		$directories = array_merge($directories, $this->includedNamespaces);

		foreach ($this->excludedNamespaces as $namespace)
			{
			unset($directories[$namespace]);
			}

		ksort($directories);

		return $this->namespaces = $directories;
		}

	/**
	 * Return all the namespaces currently indexed as an array.
	 */
	public function getAllNamespaces() : array
		{
		return array_diff(array_keys($this->namespaces), $this->excludedNamespaces);
		}

	/**
	 * Read the composer files to get all namespaces for include
	 * libraries.
	 */
	public function getAllVendorDirectories() : array
		{
		$composerJsonPath = $this->composerJsonPath;

		if (is_dir($composerJsonPath))
			{
			$composerJsonPath .= '/composer.lock';
			}
		$composerJsonPath = str_replace('//', '/', $composerJsonPath);
		$json = json_decode(@file_get_contents($composerJsonPath), true);

		if (! $json)
			{
			throw new \Exception("{$composerJsonPath} does not appear to be a valid composer.lock file");
			}
		$dir = str_replace('composer.lock', '', $composerJsonPath);
		$directories = [];

		foreach ($json['packages'] as $package)
			{
			$packagePath = $dir . 'vendor/' . $package['name'];
			$autoload = $package['autoload'] ?? [];
			$namespace = $sourceDir = '';
			$autoLoadTypes = ['psr-4', 'psr-0', 'classmap'];

			foreach ($autoLoadTypes as $type)
				{
				foreach ($autoload[$type] ?? [] as $namespace => $sourceDir)
					{
					if ('integer' == gettype($namespace))
						{
						$namespace = '\\';
						}
					// if no trailing \, then it must be root namespace
					if (false === strstr($namespace, '\\'))
						{
						$namespace = '\\';
						}
					$namespace = substr($namespace, 0, strlen($namespace) - 1);

					if (! $namespace)
						{
						$namespace = FileManager::ROOT_NAMESPACE;
						}
					$path = $packagePath . '/';
					$path = str_replace('\\', '/', $path);
					$path = str_replace('//', '/', $path);
					$directories[$namespace][FileManager::GIT] = false;
					$directories[$namespace][FileManager::REPO_ROOT] = $path;
					$directories[$namespace][] = $path . $sourceDir;
					$directories[$namespace][FileManager::CLASSES] = [];
					}
				}
			}

		return $directories;
		}

	/**
	 * Returns all files in a namespace indexed by class name.
	 */
	public function getClassesInNamespace(string $namespace) : array
		{
		if (! empty($this->namespaces[$namespace][FileManager::CLASSES]))
			{
			return $this->namespaces[$namespace][FileManager::CLASSES];
			}

		$classes = [];

		$extension = '.php';
		$files = $this->getFilesInNamespace($namespace, $extension);
		$namespaceInfo = $this->namespaces[$namespace];

		foreach ($files as $file)
			{
			$class = substr($file, strlen($namespaceInfo[0]));
			$class = str_replace('/', '\\', $class);
			if (0 === strpos($class, $namespace))
				{
				$class = substr($class, strlen($namespace));
				}
			$classes[$file] = substr($class, 0, strlen($class) - strlen($extension));
			}

		return $this->namespaces[$namespace][FileManager::CLASSES] = $classes;
		}

	/**
	 * Returns an array of files in the given namespace.  Searches
	 * all subdirectories.  Pass in an extention (starting with .)
	 * if you want to limit the search to specific directories.
	 */
	public function getFilesInNamespace(string $namespace, string $extension = '') : array
		{
		$files = [];

		if (! isset($this->namespaces[$namespace]))
			{
			throw new \Exception('In ' . __METHOD__ . " -> {$namespace} was not found.");
			}

		foreach ($this->namespaces[$namespace] as $key => $directory)
			{
			if ('integer' != gettype($key))
				{
				continue;
				}

			if (is_file($directory))
				{
				if ($this->hasExtension($filename, $extension))
					{
					$files[] = $directory;
					}
				}
			else
				{
				$directory = str_replace('\\', '/', $directory);
				$rdi = new \RecursiveDirectoryIterator($directory);
				$iterator = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::CHILD_FIRST);

				foreach ($iterator as $filename => $fileInfo)
					{
					if (! $fileInfo->isDir() && $this->hasExtension($filename, $extension))
						{
						$files[] = $filename;
						}
					}
				}
			}

		return $files;
		}

	public function getFilesInRepository($namespace, string $extension) : array
		{
		if (! empty($this->namespaces[$namespace][$extension]))
			{
			return $this->namespaces[$namespace][$extension];
			}

		$files = [];

		$directory = str_replace('\\', '/', $this->namespaces[$namespace][FileManager::REPO_ROOT]);
		$rdi = new \RecursiveDirectoryIterator($directory);
		$iterator = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($iterator as $filename => $fileInfo)
			{
			if (! $fileInfo->isDir() && $this->hasExtension($filename, $extension))
				{
				$files[] = $filename;
				}
			}

		sort($files);

		return $this->namespaces[$namespace][$extension] = $files;
		}

	/**
	 * Returns true if the namespace is in the local git repo.
	 */
	public function getGit(string $namespace) : bool
		{
		$parts = explode('\\', $namespace);

		return $this->namespaces[$parts[0]][FileManager::GIT] ?? false;
		}

	/**
	 * Load the namespace index. Pass in a specific file to load, or
	 * nothing to default to FileManager.json in the project root
	 * directory.
	 *
	 * If the file is missing, it will be regenerated and saved.
	 */
	public function load(string $file = '') : FileManager
		{
		$file = $this->getSerializedName($file);

		if (! file_exists($file))
			{
			$this->rescan();
			// load classes for each namespace
			foreach ($this->getAllNamespaces() as $namespace)
				{
				$this->getClassesInNamespace($namespace);
				$this->getFilesInRepository($namespace, '.md');
				}
			$this->save($file);
			}

		$this->namespaces = json_decode(file_get_contents($this->getSerializedName($file)), true);

		return $this;
		}

	/**
	 * Rescan the namespaces for the latest changes.
	 */
	public function rescan() : FileManager
		{
		$this->namespaces = $this->getAllNamespaceDirectories();

		return $this;
		}

	/**
	 * Save the namespace index.  Pass in a specific file to save it
	 * in, or nothing to default to FileManager.json in the project
	 * root directory.
	 */
	public function save(string $file = '') : FileManager
		{
		file_put_contents($this->getSerializedName($file), json_encode($this->namespaces));

		return $this;
		}

	private function getSerializedName(string $file) : string
		{
		if (! $file)
			{
			$class = __CLASS__;
			$file = '../' . substr($class, strrpos($class, '\\') + 1) . '.json';
			}

		return $file;
		}

	private function hasExtension(string $filename, string $extension) : bool
		{
		if (! $extension)
			{
			return true;
			}

		$retVal = strripos($filename, $extension) == strlen($filename) - strlen($extension);

		return $retVal;
		}

	}
