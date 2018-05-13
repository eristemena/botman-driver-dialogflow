<?php
namespace Tests;

use Mockery as m;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Http\Curl;
use PHPUnit_Framework_TestCase;
use Illuminate\Support\Collection;
use BotMan\Drivers\Dialogflow\DialogflowDriver;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use Dialogflow\RichMessage\Text;
use BotMan\BotMan\Exceptions\Base\BotManException;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DialogflowDriverTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    private function getDriver($responseData, $htmlInterface = null)
    {
        $request = Request::create('', 'POST', [], [], [], [
            'Content-Type: application/json',
        ], $responseData);
        if ($htmlInterface === null) {
            $htmlInterface = m::mock(Curl::class);
        }

        return new DialogflowDriver($request, [], $htmlInterface);
    }

    private function getValidDriver($requestSource = 'google', $htmlInterface = null)
    {
        if($requestSource=='google'){
            $responseData = '{"responseId":"c7a194f4-4a15-473e-a853-87ccfba97b0b","queryResult":{"queryText":"hi nama saya eris","parameters":{"given-name":""},"allRequiredParamsPresent":true,"fulfillmentMessages":[{"text":{"text":[""]}}],"outputContexts":[{"name":"projects/adam-9136f/agent/sessions/1525587949165/contexts/actions_capability_screen_output","parameters":{"given-name.original":"","given-name":""}},{"name":"projects/adam-9136f/agent/sessions/1525587949165/contexts/_actions_on_google","lifespanCount":93,"parameters":{"given-name.original":"","data":"{}","given-name":""}},{"name":"projects/adam-9136f/agent/sessions/1525587949165/contexts/actions_capability_audio_output","parameters":{"given-name.original":"","given-name":""}},{"name":"projects/adam-9136f/agent/sessions/1525587949165/contexts/google_assistant_input_type_keyboard","parameters":{"given-name.original":"","given-name":""}},{"name":"projects/adam-9136f/agent/sessions/1525587949165/contexts/actions_capability_web_browser","parameters":{"given-name.original":"","given-name":""}},{"name":"projects/adam-9136f/agent/sessions/1525587949165/contexts/actions_capability_media_response_audio","parameters":{"given-name.original":"","given-name":""}},{"name":"projects/adam-9136f/agent/sessions/1525587949165/contexts/weather","parameters":{"given-name.original":"","city":"Rome","given-name":""}}],"intent":{"name":"projects/adam-9136f/agent/intents/7ab1aeae-ab58-4bcd-b57e-a7fed8115143","displayName":"test.introduction"},"intentDetectionConfidence":1,"diagnosticInfo":{},"languageCode":"id"},"originalDetectIntentRequest":{"source":"google","version":"2","payload":{"isInSandbox":true,"surface":{"capabilities":[{"name":"actions.capability.WEB_BROWSER"},{"name":"actions.capability.MEDIA_RESPONSE_AUDIO"},{"name":"actions.capability.SCREEN_OUTPUT"},{"name":"actions.capability.AUDIO_OUTPUT"}]},"inputs":[{"rawInputs":[{"query":"hi nama saya eris","inputType":"KEYBOARD"}],"arguments":[{"rawText":"hi nama saya eris","textValue":"hi nama saya eris","name":"text"}],"intent":"actions.intent.TEXT"}],"user":{"userStorage":"{\"data\":{}}","lastSeen":"2018-05-06T02:54:10Z","locale":"id-ID","userId":"ABwppHEW9NgaT5S1NmZYR42yhs0FW1hawZHSjC_xW8FwkoZU1GMoIRAWWoThwUcA7VNX22Jzj8-KqA"},"conversation":{"conversationId":"1525587949165","type":"ACTIVE","conversationToken":"[\"_actions_on_google\",\"weather\"]"},"availableSurfaces":[{"capabilities":[{"name":"actions.capability.SCREEN_OUTPUT"},{"name":"actions.capability.AUDIO_OUTPUT"}]}]}},"session":"projects/adam-9136f/agent/sessions/1525587949165"}'
            ;
        }elseif($requestSource=='facebook'){
            $responseData = '{"responseId":"b7d5ded4-5f09-4150-b0a4-5b132728a469","queryResult":{"queryText":"tell me something funny","parameters":[],"allRequiredParamsPresent":true,"fulfillmentText":"Why do chicken walk across a street? answer what do you think? how is it funny?","fulfillmentMessages":[{"text":{"text":["When a bus drive"]}}],"outputContexts":[{"name":"projects\/newagent-6a019\/agent\/sessions\/05b84bca-b1a0-4058-859b-9ccf75834558\/contexts\/generic","lifespanCount":4,"parameters":{"facebook_sender_id":"1216233058463560"}}],"intent":{"name":"projects\/newagent-6a019\/agent\/intents\/80c2440c-b0e0-4db5-a2e2-70c5512619e2","displayName":"random.joke"},"intentDetectionConfidence":1,"diagnosticInfo":[],"languageCode":"en"},"originalDetectIntentRequest":{"source":"facebook","payload":{"data":{"sender":{"id":"1216233058463560"},"recipient":{"id":"157333794336390"},"message":{"mid":"mid.$cAACPGAVE-fxpinydvFjWTdU95891","text":"tell me something funny","seq":3764},"timestamp":1526210188732},"source":"facebook"}},"session":"projects\/newagent-6a019\/agent\/sessions\/05b84bca-b1a0-4058-859b-9ccf75834558"}
            ';
        }else{
            $responseData = '{}';
        }

        return $this->getDriver($responseData, $htmlInterface);
    }

    /** @test */
    public function it_returns_the_driver_name()
    {
        $driver = $this->getDriver(null);
        $this->assertSame('Dialogflow', $driver->getName());
    }

    /** @test */
    public function it_matches_the_request()
    {
        $driver = $this->getDriver(null);
        $this->assertFalse($driver->matchesRequest());

        $driver = $this->getValidDriver();
        $this->assertTrue($driver->matchesRequest());
    }

    /** @test */
    public function it_returns_the_message_object()
    {
        $driver = $this->getValidDriver();
        $this->assertTrue(is_array($driver->getMessages()));
    }

    /** @test */
    public function it_returns_the_messages_by_reference()
    {
        $driver = $this->getValidDriver();
        $hash = spl_object_hash($driver->getMessages()[0]);

        $this->assertSame($hash, spl_object_hash($driver->getMessages()[0]));
    }

    /** @test */
    public function it_returns_the_message_text()
    {
        $driver = $this->getValidDriver();
        $this->assertSame('test.introduction', $driver->getMessages()[0]->getText());
    }

    /** @test */
    public function it_detects_bots()
    {
        $driver = $this->getValidDriver();
        $this->assertFalse($driver->isBot());
    }

    /** @test */
    public function it_returns_the_user_id()
    {
        $driver = $this->getValidDriver();
        $this->assertSame('ABwppHEW9NgaT5S1NmZYR42yhs0FW1hawZHSjC_xW8FwkoZU1GMoIRAWWoThwUcA7VNX22Jzj8-KqA', $driver->getMessages()[0]->getSender());
    }

    /** @test */
    public function it_returns_the_channel_id()
    {
        $driver = $this->getValidDriver();
        $this->assertSame('projects/adam-9136f/agent/sessions/1525587949165', $driver->getMessages()[0]->getRecipient());
    }

    /** @test */
    public function it_returns_the_user_object()
    {
        $driver = $this->getValidDriver();

        $message = $driver->getMessages()[0];
        $user = $driver->getUser($message);

        $this->assertSame($user->getId(), 'ABwppHEW9NgaT5S1NmZYR42yhs0FW1hawZHSjC_xW8FwkoZU1GMoIRAWWoThwUcA7VNX22Jzj8-KqA');
        $this->assertNull($user->getFirstName());
        $this->assertNull($user->getLastName());
        $this->assertNull($user->getUsername());
    }

    /** @test */
    public function it_is_configured()
    {
        $driver = $this->getValidDriver();
        $this->assertTrue($driver->isConfigured());
    }

    /** @test */
    public function it_can_build_payload()
    {
        $driver = $this->getValidDriver();

        $incomingMessage = new IncomingMessage('text', '123456', '987654');

        $message = 'string';
        $payload = $driver->buildServicePayload($message, $incomingMessage);

        $this->assertSame([
            'fulfillmentMessages' => [
                [
                    "platform" => "ACTIONS_ON_GOOGLE",
                    "simpleResponses" => [
                        "simpleResponses" => [
                            [
                                "textToSpeech" => "string",
                                "displayText" => "string"
                            ]
                        ]
                    ]
                ]
            ],
            'outputContexts' => []
        ], $payload);

        $driver = $this->getValidDriver();

        $message = new OutgoingMessage('message object');
        $payload = $driver->buildServicePayload($message, $incomingMessage);

        $this->assertSame([
            'fulfillmentMessages' => [
                [
                    "platform" => "ACTIONS_ON_GOOGLE",
                    "simpleResponses" => [
                        "simpleResponses" => [
                            [
                                "textToSpeech" => "message object",
                                "displayText" => "message object"
                            ]
                        ]
                    ]
                ]
            ],
            'outputContexts' => []
        ], $payload);

        $driver = $this->getValidDriver();

        $message = Text::create()
            ->text('message object')
            ->ssml('ssml message')
        ;
        $payload = $driver->buildServicePayload($message, $incomingMessage);

        $this->assertSame([
            'fulfillmentMessages' => [
                [
                    "platform" => "ACTIONS_ON_GOOGLE",
                    "simpleResponses" => [
                        "simpleResponses" => [
                            [
                                "ssml" => "ssml message",
                                "displayText" => "message object"
                            ]
                        ]
                    ]
                ]
            ],
            'outputContexts' => []
        ], $payload);
    }


    /** @test */
    public function it_can_add_message()
    {
        $this->expectException(BotManException::class);

        $driver = $this->getValidDriver();

        $incomingMessage = new IncomingMessage('text', '123456', '987654');

        $driver->addMessage([]);
    }

    /** @test */
    public function it_can_send_payload()
    {
        $driver = $this->getValidDriver();

        $incomingMessage = new IncomingMessage('text', '123456', '987654');

        $message = 'string';
        $payload = $driver->buildServicePayload($message, $incomingMessage);

        /** @var Response $response */
        $response = $driver->sendPayload($payload);
        $this->assertSame('{"fulfillmentMessages":[{"platform":"ACTIONS_ON_GOOGLE","simpleResponses":{"simpleResponses":[{"textToSpeech":"string","displayText":"string"}]}}],"outputContexts":[]}', $response->getContent());

        $driver = $this->getValidDriver();

        $driver->addMessage('Message one');
        $driver->addMessage('Message two');

        /** @var Response $response */
        $response = $driver->sendMessage();
        $this->assertSame('{"fulfillmentMessages":[{"platform":"ACTIONS_ON_GOOGLE","simpleResponses":{"simpleResponses":[{"textToSpeech":"Message one","displayText":"Message one"}]}},{"platform":"ACTIONS_ON_GOOGLE","simpleResponses":{"simpleResponses":[{"textToSpeech":"Message two","displayText":"Message two"}]}}],"outputContexts":[]}', $response->getContent());
    }

    /** @test */
    public function it_can_get_conversation_answers()
    {
        $driver = $this->getValidDriver();

        $incomingMessage = new IncomingMessage('text', '123456', '987654');
        $answer = $driver->getConversationAnswer($incomingMessage);

        $this->assertSame('text', $answer->getText());
    }
}