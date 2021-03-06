<?php
namespace Bricks\Cli\Routing;

/**
 * Представляет запрос, использованный при вызове PHP интерпретатора из 
 * командной строки.
 *
 * Экземпляр данного класса хранит значения опций запроса и данные со 
 * стандартного потока ввода. Он может использоваться для доступа к данным, 
 * передаваемым скрипту при вызове.
 *
 * @author Artur Sh. Mamedbekov
 */
class Call{
  /**
   * @var string Адрес вызванного PHP скрипта относительно каталога, являющегося 
   * текущим в момент вызова интерпретатора.
   */
  private $name;

  /**
   * @var array Опций запроса. Если в конструкторе не определены шаблоны 
   * допустимых опций, в данном массиве значения хранятся в порядке следования, 
   * иначе используется ассоциативный массив с именами опций в качестве ключей.
   */
  private $options;

  /**
   * @var string Данные, прочитанные из потока ввода.
   */
  private $input;

  /**
   * Конструктор класса.
   *
   * Конструктор определяет шаблоны разбора допустимых опций вызова, что 
   * позволяет ассоциировать их с удобочитаемыми именами.
   * Пример создания объекта запроса, ожидающего в качестве опций однобуквенные 
   * опции '-a' и '-h', и многобуквенные опции '--all', '--hight':
   *     $call = new Call('a::h', ['all::', 'hight']);
   *
   * @param string $options [optional] Шаблон допустимых, однобуквенных (с 
   * префиксом -) опций вызова.
   * Правила определяются в шаблоне в следующем виде:
   *   - a - допустима опция '-a' без значения
   *   - a: - допустима опция '-a' с обязательным значением
   *   - a:: - допустима опция '-a' с необязательным значением
   * @param array $longoptions [optional] Шаблон допустимых, многобуквенных (с 
   * префиксом --) опций вызова.
   * Правила определяются в шаблоне в виде ассоциативного массива со следующей 
   * структурой:
   *   [
   *     'action', - допустима опция '--action' без значения
   *     'action:', - допустима опция '--action' с обязательным значением
   *     'action::', - допустима опция '--action' с необязательным значением
   *   ]
   */
  public function __construct($options = null, $longoptions = null){
    global $argv;
    $this->name = $argv[0];

    if(is_null($options)){
      $this->options = $argv;
      array_shift($this->options);
    }
    else{
      if(is_null($longoptions)){
        $longoptions = [];
      }
      $this->options = getopt($options, $longoptions);
    }
  }

  /**
   * Получает адрес вызванного PHP скрипта относительно каталога, являющегося 
   * текущим в момент вызова интерпретатора.
   *
   * @return string Адрес вызванного PHP скрипта.
   */
  public function name(){
    return $this->name;
  }

  /**
   * Получает опции вызова или значение конкретной опции.
   *
   * @param int|string $name [optional] Имя или индекс целевой опции. Если 
   * параметр не задан, возвращаются все опции вызова.
   *
   * @return string|array|null Значение целевой опции или null - если опция не 
   * задана. Если параметр не передан, возвращаются все опции вызова.
   */
  public function opt($name = null){
    if(is_null($name)){
      return $this->options;
    }

    if(!isset($this->options[$name])){
      return null;
    }

    return $this->options[$name];
  }

  /**
   * Получает значение переменной окружения.
   *
   * @param string $name Имя целевой переменной окружения.
   *
   * @return string|null Значение переменной окружения или null - если 
   * переменная не задана.
   */
  public function env($name){
    $value = getenv($name);
    if($value === false){
      return null;
    }
    return $value;
  }

  /**
   * Получает все содержимое входного потока.
   *
   * @warning Метод может привести к зависанию, если на момент вызова во входном 
   * потоке не было данных.
   *
   * @return string Содержимое входного потока.
   */
  public function input(){
    if(is_null($this->input)){
      $this->input = file_get_contents('php://stdin');
    }
    return $this->input;
  }
}
