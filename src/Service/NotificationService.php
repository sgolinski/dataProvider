<?php

namespace DataProvider\Service;

use DataProvider\Entity\Token;
use DataProvider\Factory;
use Maknz\Slack\Client as SlackClient;

class NotificationService
{
    private const HOOK = 'https://hooks.slack.com/services/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';
    private SlackClient $slack;

    public function __construct()
    {
        $this->slack = Factory::createSlackClient(self::HOOK);
    }

    public function invoke(array $tokens): void
    {
        $this->sendMessage($tokens);
    }

    public function sendMessage(array $makers)
    {
        $url = 'http://192.168.178.39/index.php/data';
        $ch = curl_init($url);
        $payload = json_encode($makers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        usleep(10000);
        $result = curl_exec($ch);
        usleep(10000);
        curl_close($ch);

        if ($result) {
            echo 'Sent alert about ' . count($makers) . ' tokens ' . date("F j, Y, g:i:s a") . PHP_EOL;
        } else {
            echo 'No response about ' . count($makers) . ' tokens ' . date("F j, Y, g:i:s a") . PHP_EOL;
        }
    }

    public function sendSlackMessage(
        array $currentRound, bool $isPotentialDrop = false
    ): void
    {
        if ($isPotentialDrop) {
            foreach ($currentRound as $coin) {
                $message = Factory::createSlackMessage()->setText($coin);
                $this->slack->sendMessage($message);
            }
        } else {
            foreach ($currentRound as $coin) {
                assert($coin instanceof Token);
                if ($coin->getChain()->asString() === 'bsc') {
                    $message = Factory::createSlackMessage()->setText($coin->alert());
                    $this->slack->sendMessage($message);
                }
            }
        }
    }

}