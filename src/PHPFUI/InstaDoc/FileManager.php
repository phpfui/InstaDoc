<?php

namespace PHPFUI\InstaDoc;

class FileManager
	{
	private $composerJsonPath = '';
	private $excludedNamespaces = [];
	private $includedNamespaces = [];

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
		$this->includedNamespaces[] = [$namespace, $directory, $localGit];

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

	public function getComposerPath() : string
		{
		return $this->composerJsonPath;
		}

	/**
	 * Load the namespace index. Pass in a specific file to load, or
	 * nothing to default to FileManager.serial in the project root
	 * directory.
	 *
	 * If the file is missing, it will be regenerated and saved.
	 */
	public function load(string $file = '') : bool
		{
		$file = $this->getSerializedName($file);

		$returnValue = true;

		if (! \PHPFUI\InstaDoc\NamespaceTree::load($file))
			{
			$this->rescan();
			$this->save($file);
			$returnValue = false;
			}

		return $returnValue;
		}

	/**
	 * Rescan the namespaces for the latest changes.
	 */
	public function rescan() : FileManager
		{
		$this->loadVendorDirectories();

		foreach ($this->includedNamespaces as $parameters)
			{
			NamespaceTree::addNameSpace($parameters[0], $parameters[1], $parameters[2]);
			}

		foreach ($this->excludedNamespaces as $namespace)
			{
			NamespaceTree::deleteNamespace($namespace);
			}

		return $this;
		}

	/**
	 * Save the namespace index. Pass in a specific file to save, or
	 * nothing to default to FileManager.serial in the project root
	 * directory.
	 */
	public function save(string $file = '') : bool
		{
		$file = $this->getSerializedName($file);

		return \PHPFUI\InstaDoc\NamespaceTree::save($file);
		}

	private function getSerializedName(string $file) : string
		{
		if (! $file)
			{
			$class = __CLASS__;
			$file = '../' . substr($class, strrpos($class, '\\') + 1) . '.serial';
			}

		return $file;
		}

	/**
	 * Read the composer files to get all namespaces for include
	 * libraries.
	 */
	private function loadVendorDirectories() : void
		{
		if (! $this->composerJsonPath)
			{
			return;
			}

		$composerJsonPath = $this->composerJsonPath . 'composer.lock';
		$composerJsonPath = str_replace('//', '/', $composerJsonPath);
		$json = json_decode(@file_get_contents($composerJsonPath), true);

		if (! $json)
			{
			throw new \Exception("{$composerJsonPath} does not appear to be a valid composer.lock file");
			}

		foreach ($json['packages'] as $package)
			{
			$packagePath = $this->composerJsonPath . 'vendor/' . $package['name'];
			$autoload = $package['autoload'] ?? [];
			$namespace = $sourceDir = '';
			$autoLoadTypes = ['psr-4', 'psr-0', 'classmap'];

			foreach ($autoLoadTypes as $type)
				{
				$path = $packagePath . '/';
				$path = str_replace('\\', '/', $path);
				$path = str_replace('//', '/', $path);

				foreach ($autoload[$type] ?? [] as $namespace => $sourceDir)
					{
					if ('psr-4' == $type)
						{
						if (is_array($sourceDir))
							{
							foreach ($sourceDir as $dir)
								{
								NamespaceTree::addNamespace($namespace, $path . $dir);
								}
							}
						else
							{
							NamespaceTree::addNamespace($namespace, $path . $sourceDir);
							}
						}
					elseif ('psr-0' == $type)
						{
						if (is_array($sourceDir))
							{
							foreach ($sourceDir as $dir)
								{
								if ($dir)
									{

									$dir .= $namespace;
									$dir = str_replace('\\', '/', $dir);
									}
								NamespaceTree::addNamespace($namespace, $path . $dir);
								}
							}
						else
							{
							if ($sourceDir)
								{
								$sourceDir .= '/' . $namespace;
								$sourceDir = str_replace('\\', '/', $sourceDir);
								$sourceDir = str_replace('//', '/', $sourceDir);
								}
							NamespaceTree::addNamespace($namespace, $path . $sourceDir);
							}
						}
					}
				}
			}
		}

	}
