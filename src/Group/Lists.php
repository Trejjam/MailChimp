<?php

namespace Trejjam\MailChimp\Group;

use GuzzleHttp;
use Nette;
use Trejjam;
use Trejjam\MailChimp;
use Schematic;

class Lists
{
	const GROUP_PREFIX         = 'lists';
	const GROUP_MEMBER_PREFIX  = '/members';
	const GROUP_SEGMENT_PREFIX = '/segments';

	/**
	 * @var Trejjam\MailChimp\Request
	 */
	private $apiRequest;

	function __construct(Trejjam\MailChimp\Request $apiRequest)
	{
		$this->apiRequest = $apiRequest;
	}

	/**
	 * @return Trejjam\MailChimp\Entity\Lists\Lists|Schematic\Entry
	 * @throws Nette\Utils\JsonException
	 */
	public function getAll()
	{
		return $this->apiRequest->get(self::GROUP_PREFIX, Trejjam\MailChimp\Entity\Lists\Lists::class);
	}

	/**
	 * @param string $listId
	 *
	 * @return Trejjam\MailChimp\Entity\Lists\ListItem|Schematic\Entry
	 * @throws Nette\Utils\JsonException
	 * @throws MailChimp\Exception\ListNotFoundException
	 */
	public function get($listId)
	{
		try {
			return $this->apiRequest->get($this->getEndpointPath($listId), Trejjam\MailChimp\Entity\Lists\ListItem::class);
		}
		catch (GuzzleHttp\Exception\ClientException $clientException) {
			throw new MailChimp\Exception\ListNotFoundException("List '{$listId}' not found", $clientException);
		}
	}

	/**
	 * @param string $listId
	 *
	 * @return MailChimp\Entity\Lists\Member\Lists|Schematic\Entry
	 * @throws Nette\Utils\JsonException
	 * @throws MailChimp\Exception\ListNotFoundException
	 */
	public function getMembers($listId)
	{
		try {
			return $this->apiRequest->get($this->getEndpointPath($listId) . self::GROUP_MEMBER_PREFIX, MailChimp\Entity\Lists\Member\Lists::class);
		}
		catch (GuzzleHttp\Exception\ClientException $clientException) {
			throw new MailChimp\Exception\ListNotFoundException("List '{$listId}' not found", $clientException);
		}
	}

	/**
	 * @param string $listId
	 * @param string $memberHash
	 *
	 * @return MailChimp\Entity\Lists\Member\MemberItem
	 * @throws Nette\Utils\JsonException
	 * @throws MailChimp\Exception\MemberNotFoundException
	 */
	public function getMember($listId, $memberHash)
	{
		try {
			return $this->apiRequest->get($this->getMemberEndpointPath($listId, $memberHash), MailChimp\Entity\Lists\Member\MemberItem::class);
		}
		catch (GuzzleHttp\Exception\ClientException $clientException) {
			throw new MailChimp\Exception\MemberNotFoundException("Member '{$memberHash}' not found in list '{$listId}' not found", $clientException);
		}
	}

	/**
	 * @param MailChimp\Entity\Lists\Member\MemberItem $memberItem
	 *
	 * @return MailChimp\Entity\Lists\Member\MemberItem|Schematic\Entry
	 * @throws Nette\Utils\JsonException
	 */
	public function addMember(MailChimp\Entity\Lists\Member\MemberItem $memberItem)
	{
		try {
			return $this->apiRequest->put(
				$this->getMemberEndpointPath(
					$memberItem->list_id,
					$memberItem->id
				),
				$memberItem->toArray(), MailChimp\Entity\Lists\Member\MemberItem::class
			);
		}
		catch (GuzzleHttp\Exception\ClientException $clientException) {
			throw new MailChimp\Exception\MemberNotFoundException("Member '{$memberItem->id}' not added into list '{$memberItem->list_id}'", $clientException);
		}
	}

	public function removeMember(MailChimp\Entity\Lists\Member\MemberItem $memberItem)
	{
		try {
			return $this->apiRequest->delete($this->getMemberEndpointPath($memberItem->list_id, $memberItem->id));
		}
		catch (GuzzleHttp\Exception\ClientException $clientException) {
			throw new MailChimp\Exception\MemberNotFoundException("Member '{$memberItem->id}' not found in list '{$memberItem->list_id}' not found", $clientException);
		}
		catch (MailChimp\Exception\RequestException $requestException) {
			if ($requestException->getCode() === 204) {
				return TRUE;
			}
			else {
				throw $requestException;
			}
		}
	}

	/**
	 * @param $listId
	 *
	 * @return Schematic\Entry|MailChimp\Entity\Lists\Segment\Lists
	 * @throws Nette\Utils\JsonException
	 * @throws MailChimp\Exception\ListNotFoundException
	 */
	public function getSegments($listId)
	{
		try {
			return $this->apiRequest->get($this->getEndpointPath($listId) . self::GROUP_SEGMENT_PREFIX, MailChimp\Entity\Lists\Segment\Lists::class);
		}
		catch (GuzzleHttp\Exception\ClientException $clientException) {
			throw new MailChimp\Exception\ListNotFoundException("List '{$listId}' not found", $clientException);
		}
	}

	/**
	 * @param string $listId
	 * @param int    $segmentId
	 *
	 * @return Schematic\Entry|MailChimp\Entity\Lists\Segment\Segment
	 * @throws Nette\Utils\JsonException
	 */
	public function getSegment($listId, $segmentId)
	{
		try {
			return $this->apiRequest->get($this->getSegmentEndpointPath($listId, $segmentId), MailChimp\Entity\Lists\Segment\Segment::class);
		}
		catch (GuzzleHttp\Exception\ClientException $clientException) {
			throw new MailChimp\Exception\ListNotFoundException("Segment '{$segmentId}' not found in list '{$listId}' not found", $clientException);
		}
	}

	/**
	 * @param int                                      $segmentId
	 * @param MailChimp\Entity\Lists\Member\MemberItem $memberItem
	 *
	 * @return Schematic\Entry|MailChimp\Entity\Lists\Member\MemberItem
	 * @throws Nette\Utils\JsonException
	 */
	public function addSegmentMember($segmentId, MailChimp\Entity\Lists\Member\MemberItem $memberItem)
	{
		try {
			return $this->apiRequest->post(
				$this->getSegmentEndpointPath($memberItem->list_id, $segmentId) . self::GROUP_MEMBER_PREFIX,
				['email_address' => $memberItem->email_address, 'status' => 'subscribed'], MailChimp\Entity\Lists\Member\MemberItem::class
			);
		}
		catch (GuzzleHttp\Exception\ClientException $clientException) {
			\Tracy\Debugger::getLogger()->log($clientException);
			throw new MailChimp\Exception\MemberNotFoundException("Member '{$memberItem->id}' not added into segment '{$segmentId}'", $clientException);
		}
	}

	private function getEndpointPath($listId)
	{
		return self::GROUP_PREFIX . "/{$listId}";
	}

	private function getMemberEndpointPath($listId, $memberHash)
	{
		return $this->getEndpointPath($listId) . self::GROUP_MEMBER_PREFIX . "/{$memberHash}";
	}

	private function getSegmentEndpointPath($listId, $segmentId)
	{
		return $this->getEndpointPath($listId) . self::GROUP_SEGMENT_PREFIX . "/{$segmentId}";
	}
}
