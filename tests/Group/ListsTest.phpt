<?php

namespace Trejjam\MailChimp\Tests\Group;

use Nette;
use Tester;
use Tester\Assert;
use Trejjam\MailChimp;

$container = require __DIR__ . '/../bootstrap.php';

class ListsTest extends Tester\TestCase
{
	private $container;

	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
	}

	public function testGetAll()
	{
		/** @var MailChimp\Group\Lists $groupLists */
		$groupLists = $this->container->getByType(MailChimp\Group\Lists::class);

		Assert::type(MailChimp\Group\Lists::class, $groupLists);

		$listsEntity = $groupLists->getAll();
		Assert::type(MailChimp\Entity\Lists\Lists::class, $listsEntity);

		$listItems = $listsEntity->getLists();

		if ($listItems->count() > 0) {
			/** @var MailChimp\Entity\Lists\ListItem $listItem */
			$listItem = $listItems->current();

			Assert::type(MailChimp\Entity\Lists\ListItem::class, $listItem);
			Assert::notSame(NULL, $listItem->id);
			Assert::type(MailChimp\Entity\Contact::class, $listItem->getContact());
			Assert::type(MailChimp\Entity\Link::class, $listItem->getLinks()->current());
		}
	}

	public function testGetEntity()
	{
		/** @var MailChimp\Group\Lists $groupLists */
		$groupLists = $this->container->getByType(MailChimp\Group\Lists::class);

		Assert::type(MailChimp\Group\Lists::class, $groupLists);

		Assert::throws(function () use ($groupLists) {
			$groupLists->get('not_exist_id');
		}, MailChimp\Exception\ListNotFoundException::class);

		$listsEntity = $groupLists->getAll();
		if ($listsEntity->getLists()->count() > 0) {
			$_listEntity = $listsEntity->getLists()->current();

			$listEntity = $groupLists->get($_listEntity->id);
			Assert::type(MailChimp\Entity\Lists\ListItem::class, $listEntity);
			Assert::notSame(NULL, $listEntity->id);
			Assert::type(MailChimp\Entity\Contact::class, $listEntity->getContact());
			Assert::type(MailChimp\Entity\Link::class, $listEntity->getLinks()->current());
		}
	}

	public function testGetEntityMembers()
	{
		/** @var MailChimp\Group\Lists $groupLists */
		$groupLists = $this->container->getByType(MailChimp\Group\Lists::class);

		Assert::throws(function () use ($groupLists) {
			$groupLists->getMembers('not_exist_id');
		}, MailChimp\Exception\ListNotFoundException::class);

		$listsEntity = $groupLists->getAll();
		if ($listsEntity->getLists()->count() > 0) {
			$_listEntity = $listsEntity->getLists()->current();

			$listMembers = $groupLists->getMembers($_listEntity->id);
			Assert::type(MailChimp\Entity\Lists\Member\Lists::class, $listMembers);
			Assert::type(MailChimp\Entity\Link::class, $listMembers->getLinks()->current());
			Assert::same($_listEntity->id, $listMembers->list_id);

			$listMemberItems = $listMembers->getMembers();

			if ($listMemberItems->count() > 0) {
				/** @var MailChimp\Entity\Lists\Member\MemberItem $listMemberItem */
				$listMemberItem = $listMemberItems->current();
				Assert::type(MailChimp\Entity\Lists\Member\MemberItem::class, $listMemberItem);
				Assert::type(MailChimp\Entity\Link::class, $listMemberItem->getLinks()->current());
				Assert::same($_listEntity->id, $listMemberItem->list_id);
			}
		}
	}
}

$test = new ListsTest($container);
$test->run();
