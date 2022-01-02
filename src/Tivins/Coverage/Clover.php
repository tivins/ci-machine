<?php

namespace Tivins\Coverage;

class Clover
{
    public int $timestamp;

    public int $files;

    public int $loc;
    public int $ncloc;

    public int $classes;

    public int $methods;
    public int $methodsCovered;

    public int $conditionals;
    public int $conditionalsCovered;

    public int $elements;
    public int $elementsCovered;

    public int $statements;
    public int $statementsCovered;

    public array $classesInfo = [];

    public function parse(string $xmlFilename): bool
    {
        $xml = simplexml_load_file($xmlFilename);
        if (!$xml) {
            return false;
        }
        $this->timestamp           = (int)$xml['generated'];
        $this->files               = (int)$xml->project->metrics['files'];
        $this->loc                 = (int)$xml->project->metrics['loc'];
        $this->ncloc               = (int)$xml->project->metrics['ncloc'];
        $this->classes             = (int)$xml->project->metrics['classes'];
        $this->methods             = (int)$xml->project->metrics['methods'];
        $this->methodsCovered      = (int)$xml->project->metrics['coveredmethods'];
        $this->elements            = (int)$xml->project->metrics['elements'];
        $this->elementsCovered     = (int)$xml->project->metrics['coveredelements'];
        $this->conditionals        = (int)$xml->project->metrics['conditionals'];
        $this->conditionalsCovered = (int)$xml->project->metrics['coveredconditionals'];
        $this->statements          = (int)$xml->project->metrics['statements'];
        $this->statementsCovered   = (int)$xml->project->metrics['coveredstatements'];

        foreach ($xml->project->file as $file) {
            $totalHits         = 0;
            $name              = (string)$file->class['name'];
            $statementsCovered = (int)$file->metrics['coveredstatements'];
            $statements        = (int)$file->metrics['statements'];

            if (!isset($file->class)) {
                continue;
            }
            foreach ($file->line as $line) {
                $totalHits += $line['count'];
            }

            $this->classesInfo[$name] = [
                'name' => $name,
                'totalHits' => $totalHits,
                'complexity' => (int)$file->class->metrics['complexity'],
                'lines' => (int)$file->metrics['loc'],
                'relevant' => $statements,
                'covered' => $statementsCovered,
                'coveredProgress' => $statements ? $statementsCovered / $statements : 1,
            ];
        }
        return true;
    }

    private function getStatementsProgress(): float
    {
        if (!$this->statementsCovered) {
            return 0;
        }
        return $this->statementsCovered / $this->statements;
    }

    private function getMethodsProgress(): float
    {
        if (!$this->methodsCovered) {
            return 0;
        }
        return $this->methodsCovered / $this->methods;
    }

    private function getConditionalsProgress(): float
    {
        if (!$this->conditionalsCovered) {
            return 0;
        }
        return $this->conditionalsCovered / $this->conditionals;
    }
}