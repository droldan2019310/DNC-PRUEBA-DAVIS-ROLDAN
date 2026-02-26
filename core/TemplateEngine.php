<?php
namespace Core;

/**
 * Wrapper PSR-4 para TemplateCls3w
 * Permite usar el sistema de templates legacy en código moderno
 *
 * Mantiene compatibilidad 100% con:
 * - Archivos .tpl en /templates/tmpl/
 * - Sistema de tags [@variable]
 * - CSS y JavaScript dinámicos
 *
 * @package Core
 */
class TemplateEngine {

    private $legacyTemplate;
    private array $data = [];
    private string $templatePath = 'templates/tmpl/';

    public function __construct() {
        // Cargar la clase legacy
        if (!class_exists('TemplateCls3w', false)) {
            require_once BASE_PATH . '/legacy/core/Template.Class.php';
        }
        $this->legacyTemplate = new \TemplateCls3w();
    }

    /**
     * Establecer el archivo template a usar
     *
     * @param string $templateFile Nombre del archivo .tpl (sin ruta)
     * @return self
     */
    public function setTemplate(string $templateFile): self {
        $this->legacyTemplate->SetTemplate($templateFile);
        return $this;
    }

    /**
     * Asignar valor a una variable del template
     *
     * @param string $key Nombre de la variable (sin [@])
     * @param mixed $value Valor a reemplazar
     * @return self
     */
    public function set(string $key, $value): self {
        $this->data[$key] = $value;
        $this->legacyTemplate->Set($key, $value);
        return $this;
    }

    /**
     * Asignar múltiples variables a la vez
     *
     * @param array $data Array asociativo de variables
     * @return self
     */
    public function setMultiple(array $data): self {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    /**
     * Agregar contenido al output
     *
     * @param string $content Contenido HTML a agregar
     * @return self
     */
    public function addOutput(string $content): self {
        $this->legacyTemplate->AddOutput($content);
        return $this;
    }

    /**
     * Retornar el HTML generado
     *
     * @return string HTML procesado
     */
    public function render(): string {
        return $this->legacyTemplate->ReturnOutput();
    }

    /**
     * Mostrar el HTML generado directamente
     *
     * @return void
     */
    public function display(): void {
        echo $this->render();
    }

    /**
     * Agregar CSS al template
     *
     * @param string $css Código CSS o ruta al archivo
     * @return self
     */
    public function addCss(string $css): self {
        $this->legacyTemplate->AddCSS($css);
        return $this;
    }

    /**
     * Agregar JavaScript al template
     *
     * @param string $js Código JS o ruta al archivo
     * @return self
     */
    public function addJs(string $js): self {
        $this->legacyTemplate->AddJS($js);
        return $this;
    }

    /**
     * Método estático para uso rápido
     *
     * @param string $templateFile Nombre del template
     * @param array $data Variables para el template
     * @return string HTML generado
     */
    public static function quick(string $templateFile, array $data = []): string {
        $engine = new self();
        $engine->setTemplate($templateFile);
        $engine->setMultiple($data);
        return $engine->render();
    }

    /**
     * Agregar JavaScript como texto
     *
     * @param string $script Código JavaScript
     * @return self
     */
    public function addScriptFText(string $script): self {
        $this->legacyTemplate->AddScriptFText($script);
        return $this;
    }

    /**
     * Agregar JavaScript desde archivo
     *
     * @param string $file Ruta al archivo JS
     * @return self
     */
    public function addScriptFile(string $file): self {
        $this->legacyTemplate->AddScriptFile($file);
        return $this;
    }

    /**
     * Retornar JavaScript agregado como texto
     *
     * @return string
     */
    public function ReturnScriptText(): string {
        return $this->legacyTemplate->ReturnScriptText();
    }

    /**
     * Retornar JavaScript agregado como archivos
     *
     * @return string
     */
    public function ReturnScriptFile(): string {
        return $this->legacyTemplate->ReturnScriptFile();
    }

    /**
     * Retornar CSS agregado
     *
     * @return string
     */
    public function ReturnCSSFiles(): string {
        return $this->legacyTemplate->ReturnCSSFiles();
    }

    /**
     * Retornar todo el output acumulado
     *
     * @return string
     */
    public function Output(): string {
        return $this->legacyTemplate->Output();
    }

    /**
     * Acceso directo a la instancia legacy si se necesita
     *
     * @return \TemplateCls3w
     */
    public function getLegacyInstance(): \TemplateCls3w {
        return $this->legacyTemplate;
    }
}
