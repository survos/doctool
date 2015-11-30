<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class JsonToMdCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('survos:json-to-md')
            ->setDescription('Convert json to md')
            ->addArgument(
                'filename',
                InputArgument::OPTIONAL,
                'path to the input file'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        if ($filename = $input->getArgument('filename')) {
            $contents = file_get_contents($filename);
        } else {
            if (0 === ftell(STDIN)) {
                $contents = '';
                while (!feof(STDIN)) {
                    $contents .= fread(STDIN, 1024);
                }
            } else {
                throw new \RuntimeException("Please provide a filename or pipe content to STDIN.");
            }
        }

        $data = json_decode($contents);
        $out = "";

        foreach ($data->commands as $command) {
            $out .= $command->name."\n";
            $out .= str_repeat('-', strlen($command->name));
            $out .= "\n\n";
            $out .= "usage: ".implode("\n", $command->usage)."\n";


            $out .= "\nDescription\n";
            $out .= str_repeat('=', strlen("description"))."\n";
            $out .= $command->description."\n";

            $arguments = $command->definition->arguments;
            $options = $command->definition->options;


            $out .= "\nArguments\n";
            $out .= str_repeat('=', strlen("Arguments"))."\n";
            foreach ($arguments as $argument) {
                $out .= "- ".$argument->name."\t*".$argument->description."*\n";
            }


            $out .= "\nOptions\n";
            $out .= str_repeat('=', strlen("Options"))."\n";
            foreach ($options as $option) {
                $out .= "- ".$option->name."\t*".$option->description."*\n";
            }
        }

//        dump($data->commands);
        $output->writeln($out);
    }
}
