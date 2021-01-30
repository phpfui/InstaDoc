<?php

namespace PHPFUI\InstaDoc\Section;

class Git extends \PHPFUI\InstaDoc\Section
	{
	public function generate(\PHPFUI\InstaDoc\PageInterface $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$gitPage = $this->controller->getParameter(\PHPFUI\InstaDoc\Controller::GIT_ONPAGE, 0);
		$limit = $this->controller->getParameter(\PHPFUI\InstaDoc\Controller::GIT_LIMIT, 20);

		$offset = $this->controller->getGitFileOffset();

		if ($offset && 0 === \strpos($fullClassPath, $offset))
			{
			$fullClassPath = \substr($fullClassPath, \strlen($offset));
			}

		try
			{
			$repo = new \Gitonomy\Git\Repository($this->controller->getGitRoot());
			$result = $repo->run('show-branch');
			}
		catch (\Exception $e)
			{
			$container->add(new \PHPFUI\SubHeader($this->controller->getGitRoot() . ' is not a valid git repo in ' . \getcwd()));
			$container->add($e->getMessage());

			return $container;
			}

		$branch = \substr($result, \strpos($result, '[') + 1, \strpos($result, ']') - 1);

		if (! $branch)
			{
			$container->add(new \PHPFUI\SubHeader("Invalid branch name: {$branch}"));

			return $container;
			}

		try
			{
			$log = $repo->getLog($branch, $fullClassPath, 0, 10);
			$count = $log->count();
			}
		catch (\Exception $e)
			{
			$container->add(new \PHPFUI\SubHeader('Git error from directory ' . \getcwd()));
			$container->add($e->getMessage());

			return $container;
			}

		$lastPage = (int)(($count - 1) / $limit) + 1;

		$log->setOffset($gitPage * $limit);
		$log->setLimit($limit);

		$table = new \PHPFUI\Table();
		$table->setHeaders(['Title', 'Name', 'Date', 'Diff']);
		$localTZ = new \DateTimeZone(\date_default_timezone_get());
		$parameters = $this->controller->getParameters();

		$commits = $log->getCommits();

		if (! \count($commits))
			{
			$container->add(new \PHPFUI\SubHeader('No commits found'));
			}

		foreach ($log->getCommits() as $commit)
			{
			$row['Title'] = $commit->getShortMessage();
			$row['Name'] = \PHPFUI\Link::email($commit->getCommitterEmail(), $commit->getCommitterName(), 'Your commit ' . $commit->getHash());
			$row['Date'] = $commit->getCommitterDate()->setTimezone($localTZ)->format('Y-m-d g:i a');
			$revealLink = new \PHPFUI\Link('#', $commit->getShortHash(), false);
			$parameters[\PHPFUI\InstaDoc\Controller::GIT_SHA1] = $commit->getHash();
			$this->getReveal($page, $revealLink, $this->controller->getUrl($parameters));
			$row['Diff'] = $revealLink;

			$table->addRow($row);
			}

		$container->add($table);

		$this->controller->setParameter(\PHPFUI\InstaDoc\Controller::GIT_LIMIT, $limit);
		$this->controller->setParameter(\PHPFUI\InstaDoc\Controller::GIT_ONPAGE, 'PAGE');

		$paginator = new \PHPFUI\Pagination($gitPage, $lastPage, $this->controller->getUrl($this->controller->getParameters()));
		$paginator->center();
		$paginator->setFastForward(3);
		$container->add($paginator);

		return $container;
		}

	private function getReveal(\PHPFUI\InstaDoc\PageInterface $page, \PHPFUI\HTML5Element $opener, string $url) : \PHPFUI\Reveal
		{
		$reveal = new \PHPFUI\Reveal($page, $opener);
		$reveal->addClass('large');
		$div = new \PHPFUI\HTML5Element('div');
		$reveal->add($div);
		$reveal->loadUrlOnOpen($url, $div->getId());

		return $reveal;
		}
	}
