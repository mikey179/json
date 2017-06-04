<?php namespace text\json;

use lang\IllegalArgumentException;

abstract class Output {
  public $format;

  /**
   * Creates a new instance
   *
   * @param  text.json.Format $format
   */
  public function __construct(Format $format= null) {
    $this->format= $format ?: Format::$DEFAULT;
  }

  /**
   * Writes a given value
   *
   * @param  var $value
   * @return self
   */
  public function write($value) {
    $f= $this->format;
    if ($value instanceof \Traversable || is_array($value)) {
      $i= 0;
      $map= null;
      foreach ($value as $key => $element) {
        if (0 === $i++) {
          $map= 0 !== $key;
          $this->appendToken($f->open($map ? '{' : '['));
        } else {
          $this->appendToken($f->comma);
        }

        if ($map) {
          $this->appendToken($f->representationOf((string)$key).$f->colon);
        }
        $this->write($element);
      }
      if (null === $map) {
        $this->appendToken('[]');
      } else {
        $this->appendToken($map ? '}' : ']');
      }
    } else {
      $this->appendToken($f->representationOf($value));
    }
    $this->close();
    return $this;
  }

  /**
   * Append a token
   *
   * @param  string $bytes
   * @return void
   */
  public abstract function appendToken($bytes);

  /**
   * Begin a sequential output stream
   *
   * @param  text.json.Types $t either Types::$OBJECT or Types::$ARRAY
   * @return text.json.Stream
   * @throws lang.IllegalArgumentException
   */
  public function begin(Types $t) {
    if ($t->isArray()) {
      return new ArrayStream($this);
    } else if ($t->isObject()) {
      return new ObjectStream($this);
    } else {
      throw new IllegalArgumentException('Expecting either an array or an object, '.$t->name().' given');
    }
  }

  /** @return void */
  public function close() {
    // Does nothing
  }

  /** @return void */
  public function __destruct() {
    $this->close();
  }
}