<?php
/**
 * Created by:  Itella Connexions ©
 * Created at:  11:32 23.04.14
 * Developer:   Pavel Kondratenko
 * Contact:     gustarus@gmail.com
 */

namespace gustarus\dropzone;

use Yii;
use gustarus\dropzone\assets\Asset;
use yii\helpers\Html;
use yii\base\Widget as BaseWidget;
use yii\web\View;

class DropzoneWidget extends BaseWidget {

  /**
   * @inheritdoc
   */
  protected $options = [
    'class' => '',
  ];

  /**
   * Параметры js.
   * @var array
   */
  protected $clientOptions = [
    'uploadMultiple' => false,
    'maxFiles' => 10,
    'data' => [],
    'selector' => false,
    'paramName' => 'file',
    'thumbnailWidth' => 100,
    'thumbnailHeight' => 100,
    'addRemoveLinks' => true,
  ];

  /**
   * Сообщения.
   * @var array
   */
  protected $clientMessages = [
    'dictDefaultMessage' => 'Click here for image uploading',
    'dictFallbackMessage' => 'Your browser does not support drag\'n\'drop file uploads.',
    'dictFallbackText' => 'Please use the fallback form below to upload your files like in the olden days.',
    'dictInvalidFileType' => 'You can\'t upload files of this type.',
    'dictFileTooBig' => 'File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.',
    'dictResponseError' => 'Server responded with {{statusCode}} code.',
    'dictCancelUpload' => 'Cancel',
    'dictCancelUploadConfirmation' => 'Are you sure you want to cancel this upload?',
    'dictRemoveFile' => 'Remove',
    'dictMaxFilesExceeded' => 'You can not upload any more files.',
  ];


  /**
   * Возвращает id дропзоны.
   * @return string
   */
  public function getZoneId() {
    return $this->getId() . '_black_whole';
  }

  /**
   * @param $options
   */
  public function setOptions($options) {
    $this->options = array_merge($this->options, $options);
  }

  /**
   * @param $options
   */
  public function setClientOptions($options) {
    $this->clientOptions = array_merge($this->clientOptions, $options);
  }

  /**
   * @param $options
   */
  public function setClientMessages($options) {
    $this->clientMessages = array_merge($this->clientMessages, $options);
  }


  /**
   * @inheritdoc
   */
  public function init() {
    parent::init();

    $this->options['id'] = $this->getId();
    $this->clientOptions['selector'] = '#' . $this->getZoneId();

    Html::addCssClass($this->options, 'dropzone');
    Html::addCssClass($this->options, $this->clientOptions['maxFiles'] == 1 ? 'dropzone-single' : 'dropzone-multiple');
  }

  /**
   * @inheritdoc
   */
  public function run() {
    $view = Yii::$app->controller->view;

    // регистрируем assets
    $asset = Asset::register($view);

    // регистрируем скрипт инициализации плагина
    $view->registerJs($this->getScript(), View::POS_READY, 'dropzone' . $this->getId());

    // настраиваем опции
    $options = $this->options;
    $options['id'] = $this->getZoneId();

    return Html::tag('div', '', $options);
  }

  /**
   * Собирает скрипт инициализации плагина.
   * @return string
   */
  public function getScript() {
    $clientOptions = self::buildClientOptions();

    $selector = $clientOptions['selector'];
    $data = $clientOptions['data'];

    unset($clientOptions['selector']);
    unset($clientOptions['data']);

    // создаем скрипт инциализации
    $source = 'var zone = new Dropzone("' . $selector . '", ' . json_encode($clientOptions) . ');';

    // csrf protection
    if (Yii::$app->request->enableCsrfValidation) {
      $data['_csrf'] = Yii::$app->request->csrfToken;
    }

    // скрипт добавления данных к форме
    if ($data) {
      $lines = [];
      foreach ($data as $key => $value) {
        // добавляем скрипт добавления данных
        $lines[] = 'formData.append(' . json_encode($key) . ', ' . json_encode($value) . ');';
      }

      $source .= 'zone.on("sending", function(file, xhr, formData) {' . implode('', $lines) . '});';
    }

    if ($this->clientOptions['maxFiles'] == 1) {
      $source .= 'zone.on("addedfile", function() {
				if(zone.files.length > 1) {
					zone.removeFile(zone.files[0]);
				}
			});';
    }

    // добавляем кастомный скрипт
    $source .= $this->getAdditionalScript();

    return '(function() {' . $source . '})();';
  }

  /**
   * Собирает дополнительные скрипты плагина.
   * @return string
   */
  public function getAdditionalScript() {
    return '';
  }


  /**
   * Собираем опции.
   * @return array
   */
  protected function buildOptions() {
    return $this->options;
  }

  /**
   * Собираем настройки.
   * @return array
   */
  protected function buildClientOptions() {
    $options = $this->clientOptions;
    $messages = $this->clientMessages;

    // переводим сообщения
    self::registerTranslations();
    foreach ($messages as &$message) {
      $message = self::t('main', $message);
    }

    return array_merge($options, $messages);
  }


  /**
   * Регистрация перевода.
   */
  public static function registerTranslations() {
    Yii::$app->i18n->translations['components/dropzone/*'] = [
      'class' => 'yii\i18n\PhpMessageSource',
      'sourceLanguage' => 'en',
      'basePath' => Yii::getAlias('@gustarus/dropzone') . '/messages',
      'fileMap' => [
        'components/dropzone/main' => 'main.php',
      ],
    ];
  }

  /**
   * @param $category
   * @param $message
   * @param array $params
   * @param null $language
   * @return string
   */
  public static function t($category, $message, $params = [], $language = null) {
    return Yii::t('components/dropzone/' . $category, $message, $params, $language);
  }
}
