<?php

namespace PHPFUI\InstaDoc\Section;

class GitDiff extends \PHPFUI\InstaDoc\Section
	{
	public function generate(\PHPFUI\InstaDoc\PageInterface $page, string $fullClassName) : \PHPFUI\Container
		{
		if (str_starts_with($fullClassName, '../'))
			{
			$fullClassName = str_replace('../', '', $fullClassName);
			}
		$repo = new \Gitonomy\Git\Repository($this->controller->getGitRoot());
		$container = new \PHPFUI\Container();

		$sha1 = $this->controller->getParameter(\PHPFUI\InstaDoc\Controller::GIT_SHA1);
		$tabSize = \str_pad('', (int)$this->controller->getParameter(\PHPFUI\InstaDoc\Controller::TAB_SIZE, '2'));

		try
			{
			$commit = $repo->getCommit($sha1);
			}
		catch (\Exception)
			{
			$container->add('Commit not found');

			return $container;
			}

		$container->add(new \PHPFUI\Header($commit->getSubjectMessage(), 4));
		$message = $commit->getBodyMessage();

		if ($message)
			{
			$callout = new \PHPFUI\Callout('secondary');
			$callout->add($message);
			$container->add($callout);
			}

		$localTZ = new \DateTimeZone(\date_default_timezone_get());
		$date = $commit->getCommitterDate()->setTimezone($localTZ)->format('Y-m-d g:i a');

		$container->add(new \PHPFUI\MultiColumn($commit->getCommitterName(), $date));

		$targetFile = \str_replace('\\', '/', $fullClassName) . '.php';
		$file = 0;
		$files = $commit->getDiff()->getFiles();

		if (empty($files))
			{
			$container->add(new \PHPFUI\Header('No diffs found for this commit.', 5));

			return $container;
			}

		foreach ($files as $file)
			{
			if ($file->getName() == $targetFile)
				{
				break;
				}
			$file = 0;
			}
		$classes = [
			\Gitonomy\Git\Diff\DiffLineType::Addition->value => 'git-added',
			\Gitonomy\Git\Diff\DiffLineType::Context->value => 'git-unchanged',
			\Gitonomy\Git\Diff\DiffLineType::Deletion->value => 'git-removed',
		];

		if ($file)
			{
			$hr = '';
			$codeBlock = new \PHPFUI\HTML5Element('pre');

			foreach ($file->getChanges() as $change)
				{
				$codeBlock->add($hr);
				$hr = '<hr>';
				foreach ($change->getDiffLines() as $line)
					{
					$span = new \PHPFUI\HTML5Element('span');
					$span->addClass($classes[$line->getType()->value]);
					$span->add(\PHPFUI\TextHelper::htmlentities(\str_replace("\t", $tabSize, $line->getContent())));
					$codeBlock->add($span . "\n");
					}
				}
			$container->add($codeBlock);
			}
		else
			{
			$container->add("{$targetFile} not found in commit");
			}

		return $container;
		}
	}
