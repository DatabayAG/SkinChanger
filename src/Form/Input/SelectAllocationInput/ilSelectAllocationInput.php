<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace SkinChanger\Form\Input\SelectAllocationInput;

use ilFormPropertyGUI;
use ILIAS\DI\Container;
use ilTemplate;
use ilTemplateException;
use ilGlyphGUI;
use ilPlugin;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;

/**
 * Class ilSelectAllocationInput
 * @package ${NAMESPACE}
 * @author  Marvin Beym <mbeym@databay.de>
 */
class ilSelectAllocationInput extends ilFormPropertyGUI
{
    /**
     * @var RequestInterface|ServerRequestInterface
     */
    protected static $request;
    /**
     * @var Container|mixed
     */
    protected $dic;

    /**
     * @var string[]
     */
    protected array $options = [];

    /**
     * @var string[]
     */
    protected array $keyOptions;

    /**
     * @var string[]
     */
    protected array $valueOptions;

    /**
     * @var string[]
     */
    protected array $tableHeaders = ["key" => "Key", "value" => "Value", "action" => "Action"];
    protected ilPlugin $plugin;

    /**
     * @var array
     */
    protected array $errors = [];

    /**
     * ilSelectAllocationInput constructor.
     * @param string   $a_title
     * @param string   $a_postvar
     * @param ilPlugin $plugin
     */
    public function __construct(ilPlugin $plugin, $a_title = "", $a_postvar = "")
    {
        global $DIC;
        $this->dic = $DIC;
        $this->lng = $GLOBALS['lng'];
        $this->plugin = $plugin;
        parent::__construct($a_title, $a_postvar);
    }

    /**
     * Sets the input to be required for the form submit to succeed.
     * @param bool $a_required
     * @return $this
     */
    public function setRequired($a_required) : ilSelectAllocationInput
    {
        parent::setRequired($a_required);
        return $this;
    }

    /** @inheritDoc */
    public function setInfo($a_info) : ilSelectAllocationInput
    {
        parent::setInfo($a_info);
        return $this;
    }

    /**
     * @param $values
     * @return void
     */
    public function setValueByArray($values) : void
    {
        $keyValuePairs = $values[$this->getPostVar()];

        $options = [];
        foreach ($keyValuePairs as $keyValuePair) {
            array_push($options, [array_keys($keyValuePair)[0] => array_values($keyValuePair)[0]]);
            //$options[array_keys($keyValuePair)[0]] = array_values($keyValuePair)[0];
        }
        $this->options = $options;
    }

    /** @inheritDoc */
    public function checkInput() : bool
    {
        $post = $this->dic->http()->request()->getParsedBody();

        if (isset($post[$this->getPostVar()]) && is_array($post[$this->getPostVar()])) {
            $keys = $post[$this->getPostVar()]["key"];
            $values = $post[$this->getPostVar()]["value"];
            if (count($keys) != count($values)) {
                $this->setAlert($this->plugin->txt("selectAllocationInput_count_not_match"));
                return false;
            }

            //Check for duplicate keys
            $foundKeys = [];
            foreach ($keys as $key) {
                $foundKeys[$key] += 1;
            }

            foreach ($foundKeys as $key => $foundOccurrences) {
                if ($foundOccurrences > 1) {
                    $this->errors[$key] = $this->plugin->txt("selectAllocationInput_already_exists");
                }
            }
        } elseif ($this->getRequired()) {
            $this->setAlert('');
            return false;
        }

        if (count($this->errors)) {
            return false;
        }
        return true;
    }

    /**
     * Inserts the input into the template.
     * @param $a_tpl
     * @throws ilTemplateException
     * @return void
     */
    public function insert($a_tpl)
    {
        $tpl = new ilTemplate($this->getFolderPath() . "tpl.selectAllocation_input.html", true, true);
        $tpl->setVariable('FIELD_ID', $this->getFieldId());
        $tpl->setVariable("KEY_HEADER", $this->tableHeaders["key"]);
        $tpl->setVariable("VALUE_HEADER", $this->tableHeaders["value"]);
        $tpl->setVariable("ACTION_HEADER", $this->tableHeaders["action"]);

        if (count($this->options) == 0) {
            array_push(
                $this->options,
                [array_keys($this->keyOptions)[0] => array_keys($this->valueOptions)[0]]
            );
        }

        //Fill options array with values from post. Used for example when form is submitted and fails.
        $postOptions = $this->dic->http()->request()->getParsedBody()[$this->getPostVar()];
        if (isset($postOptions["key"]) && count($postOptions["key"]) > count($this->options)) {
            $this->options = [];
            $keys = $postOptions["key"];
            $values = $postOptions["value"];
            for ($i = 0; $i < count($keys); $i++) {
                array_push($this->options, [$keys[$i] => $values[$i]]);
            }
        }

        //Using double array like 0: [key => value] to be able to keep double keys
        $createdOptionWithError = [];
        foreach ($this->options as $values) {
            $key = array_keys($values)[0];
            $value = array_values($values)[0];

            $tpl->setVariable("POST_VAR", $this->getPostVar());

            $this->createOptions($tpl, $this->keyOptions, $key, "keyOption");
            $this->createOptions($tpl, $this->valueOptions, $value, "valueOption");

            $tpl->setVariable("ADD_BUTTON", ilGlyphGUI::get(ilGlyphGUI::ADD));
            $tpl->setVariable("REMOVE_BUTTON", ilGlyphGUI::get(ilGlyphGUI::REMOVE));

            if (isset($this->errors[$key])) {
                if (in_array($key, $createdOptionWithError)) {
                    $tpl->setVariable('ERROR', $this->errors[$key]);
                    $tpl->parseCurrentBlock("error");
                } else {
                    array_push($createdOptionWithError, $key);
                }
            }
            $tpl->setCurrentBlock('row');
            $tpl->parseCurrentBlock();
        }

        $a_tpl->setCurrentBlock('prop_generic');
        $a_tpl->setVariable('PROP_GENERIC', $tpl->get());
        $a_tpl->parseCurrentBlock();
        $this->dic->ui()->mainTemplate()
                        ->addJavascript($this->getFolderPath() . 'selectAllocation_input.js');
        $this->dic->ui()->mainTemplate()
                        ->addCSS($this->getFolderPath() . 'selectAllocation_input.css');
    }

    /**
     * Sets the available select allocation pairs. Optional to predefine a number of allocation pairs.
     * @param string[] $options
     * @return ilSelectAllocationInput
     */
    public function setOptions(array $options) : ilSelectAllocationInput
    {
        foreach ($options as $option) {
            array_push($this->options, [$option]);
        }
        return $this;
    }

    /**
     * Sets the key options for the select input.
     * @param string[] $keyOptions
     * @return ilSelectAllocationInput
     */
    public function setKeyOptions(array $keyOptions) : ilSelectAllocationInput
    {
        $this->keyOptions = $keyOptions;
        return $this;
    }

    /**
     * Sets the value options for the select input.
     * @param string[] $valueOptions
     * @return ilSelectAllocationInput
     */
    public function setValueOptions(array $valueOptions) : ilSelectAllocationInput
    {
        $this->valueOptions = $valueOptions;
        return $this;
    }

    /**
     * @param string $keyName
     * @param string $valueName
     * @param string $actionName
     * @return $this
     */
    public function setTableHeaders(string $keyName, string $valueName, string $actionName) : ilSelectAllocationInput
    {
        $this->tableHeaders = [
            "key" => htmlspecialchars($keyName),
            "value" => htmlspecialchars($valueName),
            "action" => htmlspecialchars($actionName)
        ];
        return $this;
    }

    /**
     * Creates the select options and parses them into the template.
     * @param ilTemplate $tpl
     * @param string[]   $options
     * @param int|string $optionToBeSelected
     * @param string     $blockName
     */
    protected function createOptions(ilTemplate $tpl, array $options, $optionToBeSelected, string $blockName)
    {
        foreach ($options as $key => $value) {
            $tpl->setVariable("OPTION_VALUE", $key);
            $tpl->setVariable("OPTION_TEXT", $value);
            $tpl->setVariable("OPTION_SELECTED", $optionToBeSelected == $key ? "selected" : "");
            $tpl->setCurrentBlock($blockName);
            $tpl->parseCurrentBlock();
        }
    }

    /**
     * Returns the path to the folder where the input is located.
     * @return string
     */
    protected function getFolderPath() : string
    {
        return strstr(realpath(__DIR__), "Customizing") . "/";
    }

    /**
     * Will convert the post values from the input into key value pairs.
     * Will also remove duplicate keys keeping the first.
     * @param string $postVar
     * @return string[]|null
     * @throws Exception
     */
    public static function convertPostToKeyValuePair(string $postVar) : ?array
    {
        if (!self::$request) {
            global $DIC;
            self::$request = $DIC->http()->request();
        }

        $postData = $requestBody = self::$request->getParsedBody()[$postVar];

        $convertedKeyValuePairs = [];
        if ($postData) {
            $keys = $postData["key"];
            $values = $postData["value"];
            if (count($keys) != count($values)) {
                throw new Exception("post keys and values count dont match");
            }

            for ($i = 0; $i < count($keys); $i++) {
                $convertedKeyValuePairs[$keys[$i]] = $values[$i];
            }
        }
        return $convertedKeyValuePairs;
    }
}
