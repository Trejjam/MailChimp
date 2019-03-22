# Mailchimp

## Content

- [Configuration](#configuration)
- [Services available in DI container](#services-available-in-di-container)
- [Usage](#usage)

## Configuration

You have to register this extension at first.

```yaml
extensions:
    trejjam.mailchimp: Trejjam\MailChimp\DI\MailChimpExtension
```

List of all options:

```yaml
trejjam.mailchimp:
  findDataCenter: true/false # enabled by default, if false apiUrl mus be filled in compatible way with apiKey 
  apiUrl: 'https://%s.api.mailchimp.com/%s/' #default, first placeholder is data center extracted from apiKey, second is for API version 
  apiKey: 'someApiKey123-us11'
  lists:
    newsletter: 'foo123'
  segments:
    newsletter: 
      bar: 123
```

Minimal production configuration:

```yaml
extensions:
    trejjam.mailchimp: Trejjam\MailChimp\DI\MailChimpExtension

trejjam.mailchimp:
  apiKey: 'someApiKey123-us11'
```

## Services available in DI container

- [`Trejjam\MailChimp\Request`](https://github.com/trejjam/mailchimp/blob/master/src/Request.php)  
	This class is supposed to perform http requests with API token through `GuzzleHttp\Client`
- [`Trejjam\MailChimp\Context`](https://github.com/trejjam/mailchimp/blob/master/src/Context.php)  
	Kind of root node, contains getter for existing implementation of API scopes
- [`Trejjam\MailChimp\Group\Root`](https://github.com/trejjam/mailchimp/blob/master/src/Group/Root.php)  
	The simplest API scope, provides only one `get` method returning object with user info (related to API token) 
- [`Trejjam\MailChimp\Group\Lists`](https://github.com/trejjam/mailchimp/blob/master/src/Group/Lists.php)  
	API scope containing methods to manipulate with entities in Mailchimp lists. See class content to check available operations.
- [`Trejjam\MailChimp\Lists`](https://github.com/trejjam/mailchimp/blob/master/src/Lists.php)  
    Configuration object supposed to provide mapping between lists in Mailchimp environment and local friendly names
- [`Trejjam\MailChimp\Segments`](https://github.com/trejjam/mailchimp/blob/master/src/Segments.php)  
	Configuration object supposed to provide mapping between segments (part of a list) in Mailchimp environment and local friendly names

## Usage

Example user subscribe component

`OrderPresenter.php`:
```php
/**
 * @method onNewsletterSubscribe(Trejjam\MailChimp\Entity\Lists\Member\MemberItem $memberItem)
 * @method onNewsletterSubscribeError(Trejjam\MailChimp\Entity\Lists\Member\MemberItem $memberItem)
 */
final class MessageFactory extends UI\Component
{
	/**
	 * @var Trejjam\MailChimp\Lists
	 */
	private $mailchimpListDictionary;
	/**
	 * @var Trejjam\MailChimp\Group\Lists
	 */
	private $mailchimpList;

	/**
	 * @var callable[]
	 */
	public $onNewsletterSubscribe = [];
	/**
	 * @var callable[]
	 */
	public $onNewsletterSubscribeError = [];

	public function __construct(
		Trejjam\MailChimp\Lists $mailchimpListDictionary,
		Trejjam\MailChimp\Group\Lists $mailchimpList
	) {
		parent::__construct();

		$this->mailchimpListDictionary = $mailchimpListDictionary;
		$this->mailchimpList = $mailchimpList;
	}

	protected function createComponentNewsletter()
	{
		$form = new Nette\Application\UI\Form;

		$form->addEmail('email', 'email')
			 ->setRequired()
			 ->addRule($form::EMAIL);

		$form->addSubmit('submit', 'submit');
		$form->onSuccess[] = [$this, 'processNewsletterSubscribe'];

		return $form;
	}

	public function processNewsletterSubscribe(Nette\Application\UI\Form $form, \stdClass $values) : void
	{
		$memberItem = Trejjam\MailChimp\Entity\Lists\Member\MemberItem::create(
			$values->email,
			$this->mailchimpListDictionary->getListByName('newsletter'),
			Trejjam\MailChimp\Entity\Lists\Member\MemberItem::STATUS_PENDING
		);

		try {
			$this->mailchimpList->addMember($memberItem);

			$this->onNewsletterSubscribe($memberItem);
		} catch (Trejjam\MailChimp\Exception\MemberNotFoundException $e) {
			$this->onNewsletterSubscribeError($memberItem);
		}
	}
}
```
