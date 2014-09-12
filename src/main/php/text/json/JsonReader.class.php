<?php namespace text\json;

use text\Tokenizer;
use lang\FormatException;

/**
 * Base class for JSON readers
 */
abstract class JsonReader extends \lang\Object {

  /**
   * Reads an object
   *
   * @return [:var]
   * @throws lang.FormatException
   */
  protected function readObject() {
    $token= $this->nextToken();
    if ('}' === $token) {
      return [];
    } else if (null !== $token) {
      $this->backup($token);

      $result= [];
      do {
        $key= $this->nextValue();
        if (!is_string($key)) {
          throw new FormatException('Illegal key type '.typeof($key).', expecting string');
        }
        if (':' === ($token= $this->nextToken())) {
          $result[$key]= $this->nextValue();
        } else {
          throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading object, expecting ":"');
        }
        if ('}' === ($token= $this->nextToken())) {
          return $result;
        }
      } while (',' === $token);
    }

    throw new FormatException('Unclosed object');
  }

  /**
   * Reads a list
   *
   * @return [:var]
   * @throws lang.FormatException
   */
  protected function readList() {
    $token= $this->nextToken();
    if (']' === $token) {
      return [];
    } else if (null !== $token) {
      $this->backup($token);

      $result= [];
      do {
        $result[]= $this->nextValue();
        if (']' === ($token= $this->nextToken())) {
          return $result;
        }
      } while (',' === $token);
    }

    throw new FormatException('Unclosed list');
  }

  /**
   * Reads a string
   *
   * @return string
   * @throws lang.FormatException
   */
  protected function expand($str) {
    if ('"' !== $str{strlen($str) - 1}) {
      throw new FormatException('Unclosed string');
    }

    return substr($str, 1, -1);
  }

  /**
   * Pushes back a given byte sequence to be retokenized
   *
   * @param  string $bytes
   * @return void
   */
  public abstract function backup($bytes);

  /**
   * Returns next token
   *
   * @return string
   */
  public abstract function nextToken();

  /**
   * Reads a value
   *
   * @return var
   * @throws lang.FormatException
   */
  public function nextValue() {
    static $keyword= [
      'true'   => [true],
      'false'  => [false],
      'null'   => [null],
    ];

    $token= $this->nextToken();
    if ('{' === $token) {
      return $this->readObject();
    } else if ('[' === $token) {
      return $this->readList();
    } else if ('"' === $token{0}) {
      return $this->expand($token);
    } else if (isset($keyword[$token])) {
      return $keyword[$token][0];
    } else if (is_numeric($token)) {
      return $token > PHP_INT_MAX || $token < -PHP_INT_MAX- 1 || strcspn($token, '.eE') < strlen($token)
        ? (double)$token
        : (int)$token
      ;
    } else {
      throw new FormatException('Unexpected token ['.\xp::stringOf($token).'] reading value');
    }

    throw new FormatException('Empty input');
  }

  /**
   * Reads a value from an input stream
   *
   * @return var
   */
  public function read() {
    $value= $this->nextValue();

    if (null !== ($token= $this->nextToken())) {
      throw new FormatException('Junk after end of value ['.\xp::stringOf($token).']');
    }

    return $value;
  }

  /**
   * Reads elements from an input stream sequentially
   *
   * @return var
   */
  public function elements() {
    return new Elements($this);
  }

  /**
   * Reads key/value pairs from an input stream sequentially
   *
   * @return var
   */
  public function pairs() {
    return new Pairs($this);
  }
}