# BotMan Dialogflow Driver

BotMan driver to handle Dialogflow fulfillment with [BotMan](https://github.com/botman/botman).

It uses [`eristemena/dialog-fulfillment-webhook-php`](https://github.com/eristemena/dialog-fulfillment-webhook-php) library, so it supports v1 and v2 of Dialogflow [request](https://dialogflow.com/docs/reference/v2-comparison).

## Installation & Setup

First you need to pull in the Driver. 

```
composer require eristemena/botman-driver-dialogflow
```

If you're using BotMan Studio, that's pretty much it.

But if you don't, then load the driver before creating the BotMan instance:

```
DriverManager::loadDriver(\BotMan\Drivers\Dialogflow\DialogflowDriver::class);

// Create BotMan instance
BotManFactory::create([]);
```

## Usage

### Hearing Messages

You can start receiving message using `hears()` based on the [Intent](https://dialogflow.com/docs/intents) of the message,

```
$botman->hears('Intent Name', function ($botman) {
    // replies here
});
```

### Single Message Reply

The simplest way to reply to an incoming message is using BotMan's own `reply()` method:

```
$botman->hears('Default Welcome Intent', function ($botman) {
    $botman->reply('Hi, welcome!');
});
```

### Multiple Message Replies

Normally when you want to send multiple replies, you use `reply()` multiple times. Unfortunately this doesn't work for Dialogflow driver, cause the messages should be in a single [response](https://dialogflow.com/docs/fulfillment#response) payload.

For that, you have to use specific methods for this driver `addMessage()` and `sendMessage()` as follow,

```
$botman->hears('Default Welcome Intent', function ($botman) {
    $botman->addMessage('Good morning');
    $botman->addMessage('How may i help you?');
    $botman->sendMessage();
});
```

### Rich Messages

#### [Text](https://dialogflow.com/docs/rich-messages#text)

Use [`Dialogflow\RichMessage\Text`](https://github.com/eristemena/dialog-fulfillment-webhook-php/blob/master/docs/RichMessage/Text.md)

```
    $text = Text::create()
        ->text('Hello')
        ->ssml('
            <speak>
                Hello!
                <audio src="https://actions.google.com/sounds/v1/cartoon/clang_and_wobble.ogg"></audio>
            </speak>
        ')
    ;

    $botman->reply($text);
```

#### [Image](https://dialogflow.com/docs/rich-messages#image)

Use [`Dialogflow\RichMessage\Image`](https://github.com/eristemena/dialog-fulfillment-webhook-php/blob/master/docs/RichMessage/Image.md)

```
    $image = Image::create('https://picsum.photos/200/300');
    $botman
        ->addMessage('This is an image')
        ->addMessage($image)
        ->sendMessage()
    ;
```

#### [Card](https://dialogflow.com/docs/rich-messages#card)

Use [`Dialogflow\RichMessage\Card`](https://github.com/eristemena/dialog-fulfillment-webhook-php/blob/master/docs/RichMessage/Card.md)

```
    $card = Card::create()
        ->title('This is title')
        ->text('This is text body, you can put whatever here.')
        ->image('https://picsum.photos/200/300')
        ->button('This is a button', 'https://docs.dialogflow.com/')
    ;

    $botman
        ->addMessage('This is a card')
        ->addMessage($card)
        ->sendMessage()
    ;
```

#### [Quick Replies](https://dialogflow.com/docs/rich-messages#quick_replies)

Use [`Dialogflow\RichMessage\Suggestion`](https://github.com/eristemena/dialog-fulfillment-webhook-php/blob/master/docs/RichMessage/Suggestion.md)

```
    $suggestion = Suggestion::create(['Tell me a joke', 'Tell me about yourself']);

    $botman
        ->addMessage('Hi, how can i help you with?')
        ->addMessage($suggestion)
        ->sendMessage()
    ;
```

#### [Custom Payload](https://dialogflow.com/docs/rich-messages#custom_payload)

Use [`Dialogflow\RichMessage\Payload`](https://github.com/eristemena/dialog-fulfillment-webhook-php/blob/master/docs/RichMessage/Payload.md)

```
    $payload = Payload::create([
        'expectUserResponse' => false
    ]);

    $botman
        ->addMessage('Have a good day')
        ->addMessage($payload)
        ->sendMessage()
    ;
```
