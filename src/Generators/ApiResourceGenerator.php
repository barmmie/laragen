<?php

namespace InfyOm\Generator\Generators;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Utils\FileUtil;

class ApiResourceGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $fileName;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathResource;
        $this->fileName = $this->commandData->modelName.'Resource.php';
    }

    public function generate()
    {
        $templateData = get_template('resource', 'laravel-generator');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $docsTemplate = get_template('docs.resource', 'laravel-generator');
        $docsTemplate = fill_template($this->commandData->dynamicVars, $docsTemplate);
        $docsTemplate = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $docsTemplate);

        $templateData = str_replace('$DOCS$', $docsTemplate, $templateData);
        $templateData = str_replace('$FIELDS$', $this->generateFields(), $templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\n Resource created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Service file deleted: '.$this->fileName);
        }
    }

    private function generateFields()
    {
        $fields = [];

        foreach ($this->commandData->fields as $field) {

                $fields[] = "'".$field->name. "' => ". '$this->'.$field->name.',';

        }

        foreach ($this->commandData->relations as $relation) {
            list($functionName, $relation, $relationClass, $modelName) = $relation->getRelationDetails();
            $pluralModelName = str_plural($modelName);

            $fields[] = "'".$functionName. "' => ". 'new '.$pluralModelName.'Resource($this->whenLoaded(\''.$functionName.'\')),';

        }

        return implode(infy_nl_tab(1, 3), array_merge($fields));
    }
}
