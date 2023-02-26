<?php

namespace Exan\StabilityBot;

use Carbon\Carbon;
use Exan\Fenrir\Bitwise\Bitwise;
use Exan\Fenrir\Command\FiredCommand;
use Exan\Fenrir\Discord;
use Exan\Fenrir\Enums\Gateway\Intents;
use Exan\Fenrir\Enums\Parts\ApplicationCommandTypes;
use Exan\Fenrir\Rest\Helpers\Command\CommandBuilder;
use Psr\Log\LoggerInterface;

class StabilityBot
{
    public Discord $discord;
    private Carbon $startTime;

    public function __construct(
        private string $token,
        private LoggerInterface $logger,
        private string $libraryVersion
    ) {
        $this->discord = (new Discord(
            $token,
            Bitwise::from(...$this->getIntents()),
            $logger
        ))
            ->withGateway()
            ->withRest()
            ->withCommandHandler('575395886117421083');


        $this->startTime = new Carbon();
    }

    public function register()
    {
        $this->discord->command->registerCommand(
            (new CommandBuilder)
                ->setName('status')
                ->setDescription('Generate a status report')
                ->setType(ApplicationCommandTypes::CHAT_INPUT),
            function (FiredCommand $command) {
                $report = new Report(
                    $this->libraryVersion,
                    $this->startTime
                );

                $command->sendFollowUpMessage($report->toInteractionCallback());
            }
        );
    }

    public function getIntents(): array
    {
        return [
            Intents::GUILD_MESSAGES,
            Intents::DIRECT_MESSAGES,
        ];
    }
}
