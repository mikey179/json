<?php namespace text\json\unittest;

use text\json\JsonReader;
use io\streams\MemoryInputStream;

/**
 * Test JSON reader
 *
 * @see   https://bugs.php.net/bug.php?id=41504
 * @see   https://bugs.php.net/bug.php?id=45791
 * @see   https://bugs.php.net/bug.php?id=54484
 * @see   https://github.com/xp-framework/xp-framework/issues/189
 */
class JsonReaderTest extends \unittest\TestCase {

  #[@test]
  public function can_create() {
    new JsonReader();
  }

  #[@test, @values([
  #  ['', '""'],
  #  ['Test', '"Test"'],
  #  ['Test the "west"', '"Test the \"west\""'],
  #  ['€uro', '"\u20acuro"'], ['€uro', '"\u20ACuro"'],
  #  ['Knüper', '"Knüper"'], ['Knüper', '"Kn\u00fcper"'],
  #  ["Test\b", '"Test\b"'],
  #  ["Test\f", '"Test\f"'],
  #  ["Test\n", '"Test\n"'],
  #  ["Test\r", '"Test\r"'],
  #  ["Test\t", '"Test\t"'],
  #  ["Test\\", '"Test\\\\"'],
  #  ["Test/", '"Test\/"']
  #])]
  public function read_string($expected, $source) {
    $this->assertEquals($expected, (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test, @expect('lang.FormatException')]
  public function illegal_escape_sequence() {
    (new JsonReader())->read(new MemoryInputStream('"\X"'));
  }

  #[@test, @expect('lang.FormatException')]
  public function illegal_encoding() {
    (new JsonReader())->read(new MemoryInputStream("\"\xfc\""));
  }

  #[@test]
  public function read_iso_8859_1() {
    $this->assertEquals('ü', (new JsonReader('iso-8859-1'))->read(new MemoryInputStream("\"\xfc\"")));
  }

  #[@test, @expect('lang.FormatException'), @values([
  #  '"', '"abc', '"abc\"'
  #])]
  public function unclosed_string($source) {
    (new JsonReader())->read(new MemoryInputStream($source));
  }

  #[@test, @values([
  #  [0, '0'],
  #  [1, '1'],
  #  [-1, '-1']
  #])]
  public function read_integer($expected, $source) {
    $this->assertEquals($expected, (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test]
  public function read_int_max() {
    $n= PHP_INT_MAX;
    $this->assertEquals($n, (new JsonReader())->read(new MemoryInputStream((string)$n)));
  }

  #[@test]
  public function read_int_min() {
    $n= -PHP_INT_MAX -1;
    $this->assertEquals($n, (new JsonReader())->read(new MemoryInputStream((string)$n)));
  }

  #[@test, @values([
  #  [0.0, '0.0'],
  #  [1.0, '1.0'],
  #  [0.5, '0.5'],
  #  [-1.0, '-1.0'],
  #  [-0.5, '-0.5'],
  #  [0.0000000001, '0.0000000001'],
  #  [9999999999999999999999999999999999999.0, '9999999999999999999999999999999999999'],
  #  [-9999999999999999999999999999999999999.0, '-9999999999999999999999999999999999999']
  #])]
  public function read_double($expected, $source) {
    $this->assertEquals($expected, (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test, @values([
  #  [10.0, '1E1'], [10.0, '1E+1'], [10.0, '1e1'], [10.0, '1e+1'],
  #  [-10.0, '-1E1'], [-10.0, '-1E+1'], [-10.0, '-1e1'], [-10.0, '-1e+1'],
  #  [0.1, '1E-1'], [0.1, '1e-1'],
  #  [-0.1, '-1E-1'], [0.1, '1e-1'],
  #  [0.0, '0E0'], [0.0, '0e0'],
  #  [10000000000, '1E10'], [10000000000, '1e10'],
  #  [-10000000000, '-1E10'], [-10000000000, '-1e10']
  #])]
  public function read_exponent($expected, $source) {
    $this->assertEquals($expected, (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test, @values([
  #  [true, 'true'],
  #  [false, 'false'],
  #  [null, 'null']
  #])]
  public function read_keyword($expected, $source) {
    $this->assertEquals($expected, (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test, @values(['{}', '{ }'])]
  public function read_empty_object($source) {
    $this->assertEquals([], (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test, @values([
  #  '{"key": "value"}',
  #  '{"key" : "value"}',
  #  '{ "key" : "value" }'
  #])]
  public function read_key_value_pair($source) {
    $this->assertEquals(['key' => 'value'], (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test, @values([
  #  '{"a": "v1", "b": "v2"}',
  #  '{"a" : "v1", "b" : "v2"}',
  #  '{ "a" : "v1" , "b" : "v2" }'
  #])]
  public function read_key_value_pairs($source) {
    $this->assertEquals(['a' => 'v1', 'b' => 'v2'], (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test, @values([
  #  '{"": "value"}',
  #  '{"" : "value"}',
  #  '{ "" : "value" }'
  #])]
  public function empty_key($source) {
    $this->assertEquals(['' => 'value'], (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test, @expect('lang.FormatException'), @values([
  #  '{', '{{', '{{}',
  #  '}', '}}'
  #])]
  public function unclosed_object($source) {
    (new JsonReader())->read(new MemoryInputStream($source));
  }

  #[@test, @expect('lang.FormatException')]
  public function missing_key() {
    (new JsonReader())->read(new MemoryInputStream('{:"value"}'));
  }

  #[@test, @expect('lang.FormatException')]
  public function missing_value() {
    (new JsonReader())->read(new MemoryInputStream('{"key":}'));
  }

  #[@test, @expect('lang.FormatException')]
  public function missing_key_and_value() {
    (new JsonReader())->read(new MemoryInputStream('{:}'));
  }

  #[@test, @expect('lang.FormatException')]
  public function missing_colon() {
    (new JsonReader())->read(new MemoryInputStream('{"key"}'));
  }

  #[@test, @expect('lang.FormatException')]
  public function missing_comma_between_key_value_pairs() {
    (new JsonReader())->read(new MemoryInputStream('{"a": "v1" "b": "v2"}'));
  }

  #[@test, @expect('lang.FormatException')]
  public function trailing_comma_in_object() {
    (new JsonReader())->read(new MemoryInputStream('{"key": "value",}'));
  }

  #[@test, @expect('lang.FormatException'), @values([
  #  '{1: "value"}',
  #  '{1.0: "value"}',
  #  '{true: "value"}', '{false: "value"}', '{null: "value"}',
  #  '{[]: "value"}', '{["a"]: "value"}',
  #  '{{}: "value"}', '{{"a": "b"}: "value"}'
  #])]
  public function illegal_key($source) {
    (new JsonReader())->read(new MemoryInputStream($source));
  }

  #[@test, @values(['[]', '[ ]'])]
  public function read_empty_array($source) {
    $this->assertEquals([], (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test, @values([
  #  '["value"]',
  #  '[ "value" ]'
  #])]
  public function read_list_with_value($source) {
    $this->assertEquals(['value'], (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test, @values([
  #  '["v1","v2"]',
  #  '["v1", "v2"]',
  #  '[ "v1", "v2" ]'
  #])]
  public function read_list_with_values($source) {
    $this->assertEquals(['v1', 'v2'], (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test, @values([
  #  '["v1",["v2","v3"]]',
  #  '["v1", ["v2", "v3"]]',
  #  '[ "v1" , [ "v2" , "v3" ] ]'
  #])]
  public function read_list_with_nested_list($source) {
    $this->assertEquals(['v1', ['v2', 'v3']], (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test, @expect('lang.FormatException'), @values([
  #  '[', '[[', '[[]',
  #  ']', ']]'
  #])]
  public function unclosed_array($source) {
    (new JsonReader())->read(new MemoryInputStream($source));
  }

  #[@test, @expect('lang.FormatException')]
  public function missing_comma_after_value() {
    (new JsonReader())->read(new MemoryInputStream('["v1" "v2"]'));
  }

  #[@test, @expect('lang.FormatException')]
  public function trailing_comma_in_array() {
    (new JsonReader())->read(new MemoryInputStream('["value",]'));
  }

  #[@test, @expect('lang.FormatException'), @values(['', ' ', '  '])]
  public function empty_input($source) {
    (new JsonReader())->read(new MemoryInputStream($source));
  }

  #[@test, @expect('lang.FormatException')]
  public function xml_input() {
    (new JsonReader())->read(new MemoryInputStream('<xml version="1.0"?><document/>'));
  }

  #[@test, @expect('lang.FormatException'), @values([
  #  'UNRECOGNIZED_CONSTANT',
  #  "'json does not allow single quoted strings'",
  #  '<>',
  #  '0.00.1',
  #  '0-10',
  #  '"a" "b"',
  #  '"a", "b"'
  #])]
  public function illegal_token($source) {
    (new JsonReader())->read(new MemoryInputStream($source));
  }

  #[@test, @values([
  #  " [1] ", "  [1]",
  #  "\r[1]", "\r\n[1]",
  #  "\n[1]", "\n\n[1]",
  #  "\t[1]", "\t \t [1]"
  #])]
  public function leading_whitespace_is_ok($source) {
    $this->assertEquals([1], (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test, @values([
  #  "[1] ", "[1]  ",
  #  "[1]\r", "[1]\r\n",
  #  "[1]\n", "[1]\n\n",
  #  "[1]\t", "[1]\t \t "
  #])]
  public function trailing_whitespace_is_ok($source) {
    $this->assertEquals([1], (new JsonReader())->read(new MemoryInputStream($source)));
  }

  #[@test]
  public function indented_json() {
    $this->assertEquals(
      [
        'color' => 'green',
        'sizes' => ['S', 'M', 'L', 'XL'],
        'price' => 12.99
      ],
      (new JsonReader())->read(new MemoryInputStream('{
        "color" : "green",
        "sizes" : [ "S", "M", "L", "XL" ],
        "price" : 12.99
      }'))
    );
  }
}