<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class JsonToMdCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('survos:json-to-md')
            ->setDescription('Convert json to md')
            ->addOption(
                'filename',
                null,
                InputOption::VALUE_OPTIONAL,
                'path to the input file',
                null
            )
            ->addOption(
                'output-dir',
                null,
                InputOption::VALUE_OPTIONAL,
                'path to the output folder',
                'output'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputPath = $this->getContainer()->getParameter('kernel.root_dir').'/'.$input->getOption('output-dir');

        if (!file_exists($outputPath)) {
            mkdir($output, 0777, true);
        }

        if ($filename = $input->getOption('filename')) {
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


        $groupedCommands = [];

        foreach ($data->commands as $command) {
            $outMd = "";
            $outMd .= $command->name."\n";
            $outMd .= str_repeat('-', strlen($command->name));
            $outMd .= "\n\n";
            $outMd .= "usage: ".implode("\n", $command->usage)."\n";


            $outMd .= "\nDescription\n";
            $outMd .= str_repeat('=', strlen("description"))."\n";
            $outMd .= $command->description."\n";

            $arguments = $command->definition->arguments;
            $options = $command->definition->options;


            $outMd .= "\nArguments\n";
            $outMd .= str_repeat('=', strlen("Arguments"))."\n";
            foreach ($arguments as $argument) {
                $outMd .= "- ".$argument->name."\t*".$argument->description."*\n";
            }


            $outMd .= "\nOptions\n";
            $outMd .= str_repeat('=', strlen("Options"))."\n";
            foreach ($options as $option) {
                $outMd .= "- ".$option->name."\t*".$option->description."*\n";
            }

            $name = explode(':', $command->name);
            $name = reset($name);
            if (!isset($groupedCommands[$name])) {
                $groupedCommands[$name] = '';
            }

            $groupedCommands[$name] .= $outMd."\n\n";
        }

        foreach ($groupedCommands as $name => $outMd) {
            $file = $outputPath."/{$name}-commands.md";
            file_put_contents($file, $outMd);
        }
//        dump($data->commands);
//        $output->writeln($out);
    }
}
