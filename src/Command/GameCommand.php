<?php

declare(strict_types=1);

namespace App\Command;

use App\Character\Character;
use App\Character\CharacterType;
use App\Fight;
use App\Game;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:game:play')]
class GameCommand extends Command
{
    public function __construct(
        private readonly Game $game,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->text('Welcome to the game where warriors fight against each other for honor and glory... and 🍕!');
        /** @var string $characterChoice */
        $characterChoice = $io->choice('Select your character', CharacterType::choices());

        $playerCharacter = $this->game->createCharacter(CharacterType::from($characterChoice));
        $playerCharacter->setNickname('Player ' . $characterChoice);

        $io->writeln('It\'s time for a fight!');

        $this->play($io, $playerCharacter);

        return Command::SUCCESS;
    }

    private function play(SymfonyStyle $io, Character $player): void
    {
        do {
            $aiCharacter = $this->selectEnemyCharacter();

            $io->writeln(sprintf('Opponent found <comment>%s</comment>', $aiCharacter->getNickname()));

            $fightResult = $this->game->play($player, $aiCharacter);

            $this->printResult($fightResult, $player, $io);

            $answer = $io->choice('Want to keep playing?', [
                1 => 'Fight!',
                2 => 'Exit Game',
            ]);
        } while ($answer === 'Fight!');
    }

    private function selectEnemyCharacter(): Character
    {
        $characterTypes = CharacterType::cases();
        $enemyCharacterType = $characterTypes[array_rand($characterTypes)];

        $enemyCharacter = $this->game->createCharacter($enemyCharacterType);
        $enemyCharacter->setNickname('AI: ' . $enemyCharacterType->name());

        return $enemyCharacter;
    }

    private function printResult(Fight $fight, Character $player, SymfonyStyle $io): void
    {
        // let's make it *feel* like a proper battle!
        $weapons = ['🛡', '⚔️', '🏹'];
        $io->writeln(['']);
        $io->write('(queue epic battle sounds) ');
        for ($i = 0; $i < $fight->getRounds(); $i++) {
            $io->write($weapons[array_rand($weapons)]);
            usleep(300000);
        }
        $io->writeln('');

        $io->writeln('------------------------------');
        if ($fight->getWinner() === $player) {
            $io->writeln('Result: <bg=green;fg=white>You WON!</>');
        } else {
            $io->writeln('Result: <bg=red;fg=white>You lost...</>');
        }

        $io->writeln('Total Rounds: ' . $fight->getRounds());
        $io->writeln('Damage dealt: ' . $fight->getDamageDealt());
        $io->writeln('Damage received: ' . $fight->getDamageReceived());
        $io->writeln('XP: ' . $player->getXp());
        $io->writeln('Level: ' . $player->getLevel());
        $io->writeln('Exhausted Turns: ' . $fight->getExhaustedTurns());
        $io->writeln('------------------------------');
    }
}
