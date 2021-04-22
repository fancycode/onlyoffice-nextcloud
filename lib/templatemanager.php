<?php
/**
 *
 * (c) Copyright Ascensio System SIA 2021
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

namespace OCA\Onlyoffice;


/**
 * Template manager
 *
 * @package OCA\Onlyoffice
 */
class TemplateManager {

    /**
     * Application name
     *
     * @var string
     */
    private static $appName = "onlyoffice";

    /**
     * Mapping local path to templates
     *
     * @var Array
     */
    private static $localPath = [
        "az" => "az-Latn-AZ",
        "bg" => "bg-BG",
        "cs" => "cs-CZ",
        "de" => "de-DE",
        "de_DE" => "de-DE",
        "el" => "el-GR",
        "en" => "en-US",
        "en_GB" => "en-GB",
        "es" => "es-ES",
        "fr" => "fr-FR",
        "it" => "it-IT",
        "ja" => "ja-JP",
        "ko" => "ko-KR",
        "lv" => "lv-LV",
        "nl" => "nl-NL",
        "pl" => "pl-PL",
        "pt_BR" => "pt-BR",
        "pt_PT" => "pt-PT",
        "ru" => "ru-RU",
        "sk" => "sk-SK",
        "sv" => "sv-SE",
        "uk" => "uk-UA",
        "vi" => "vi-VN",
        "zh_CN" => "zh-CN"
    ];

    /**
     * Get template
     *
     * @param string $name - file name
     *
     * @return string
     */
    public static function GetTemplate(string $name) {
        $ext = strtolower("." . pathinfo($name, PATHINFO_EXTENSION));

        $lang = \OC::$server->getL10NFactory("")->get("")->getLanguageCode();

        $templatePath = self::getTemplatePath($lang, $ext);

        $template = file_get_contents($templatePath);
        return $template;
    }

    /**
     * Get global template directory
     *
     * @return Folder
     */
    public static function GetGlobalTemplateDir() {
        $rootFolder = \OC::$server->getRootFolder();

        $appData = $rootFolder->get("appdata_" . \OC::$server->getConfig()->GetSystemValue("instanceid", null));

        $appDir = $appData->nodeExists("onlyoffice") ? $appData->get("onlyoffice") : $appData->newFolder("onlyoffice");
        $templateDir = $appDir->nodeExists("template") ? $appDir->get("template") : $appDir->newFolder("template");

        return $templateDir;
    }

    /**
     * Get global templates
     *
     * @param string $type - template format type
     *
     * @return array
     */
    public static function GetGlobalTemplates($type = null) {
        $templates = [];
        $templateDir = self::GetGlobalTemplateDir();

        if (!empty($type)) {
            $mime = self::GetMimeTemplate($type);
            $templatesList = $templateDir->searchByMime($mime);

        } else {
            $templatesList = $templateDir->getDirectoryListing();
        }

        foreach ($templatesList as $templatesItem) {
            $template = [
                "id" => $templatesItem->getId(),
                "name" => $templatesItem->getName(),
                "type" => TemplateManager::GetTypeTemplate($templatesItem->getMimeType())
            ];
            array_push($templates, $template);
        }

        return $templates;
    }

    /**
     * Get template content
     *
     * @param string $templateId - identifier file template
     *
     * @return string
     */
    public static function GetGlobalTemplate($templateId) {
        $logger = \OC::$server->getLogger();

        $templateDir = self::GetGlobalTemplateDir();
        try {
            $templates = $templateDir->getById($templateId);
        } catch(\Exception $e) {
            $logger->logException($e, ["message" => "GetGlobalTemplate: $templateId", "app" => self::$appName]);
            return null;
        }

        if (empty($templates)) {
            $logger->info("Template not found: $templateId", ["app" => self::$appName]);
            return null;
        }

        $content = $templates[0]->getContent();

        return $content;
    }

    /**
     * Get type template from mimetype
     *
     * @param string $mime - mimetype
     *
     * @return string
     */
    public static function GetTypeTemplate($mime) {
        switch($mime) {
            case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
                return "document";
            case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
                return "spreadsheet";
            case "application/vnd.openxmlformats-officedocument.presentationml.presentation":
                return "presentation";
        }

        return "";
    }

    /**
     * Get mimetype template from format type
     *
     * @param string $type - format type
     *
     * @return string
     */
    public static function GetMimeTemplate($type) {
        switch($type) {
            case "document":
                return "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
            case "spreadsheet":
                return "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
            case "presentation":
                return "application/vnd.openxmlformats-officedocument.presentationml.presentation";
        }

        return "";
    }

    /**
     * Check template type
     *
     * @param string $name - template name
     *
     * @return bool
     */
    public static function IsTemplateType($name) {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        switch($ext) {
            case "docx":
            case "xlsx":
            case "pptx":
                return true;
        }

        return false;
    }

    /**
     * Get template path
     *
     * @param string $lang - language
     * @param string $ext - file extension
     *
     * @return string
     */
    public static function GetTemplatePath(string $lang, string $ext) {
        if (!array_key_exists($lang, self::$localPath)) {
            $lang = "en";
        }

        return dirname(__DIR__) . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . self::$localPath[$lang] . DIRECTORY_SEPARATOR . "new" . $ext;
    }
}
