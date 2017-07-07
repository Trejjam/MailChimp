<?php

namespace Trejjam\MailChimp\Exception;

use Psr;

class RequestException extends \LogicException
{
	/**
	 * @var Psr\Http\Message\ResponseInterface
	 */
	private $httpResponse;

	/**
	 * @param Psr\Http\Message\ResponseInterface $httpResponse
	 *
	 * @return $this
	 * @internal
	 */
	public function setResponse(Psr\Http\Message\ResponseInterface $httpResponse)
	{
		$this->httpResponse = $httpResponse;

		return $this;
	}

	/**
	 * @return Psr\Http\Message\ResponseInterface
	 */
	public function getResponse()
	{
		return $this->httpResponse;
	}
}