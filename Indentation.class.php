<?php
# Roger 10. feb. 2014 14:04:00

/** Generic text indentation utilities
 *
 * These methods require multiline strings as input, except the blocks()
 * method, which will accept both arrays and strings.
 *
 * Technical NOTE: CR characters are mostly ignored, when splitting on LF the
 * CR remains as the last character of each line and does not affect indentation.
 *
 * TAB characters are recognized, but they count as one, which makes it hard
 * to see the correct level when used in combination with spaces. For this
 * reason you should use either spaces or TAB characters, not both mixed.
 *
 * blocks($raw_lines) -- Input multiline string or array of strings, returns
 *                       array of blocks paired with line number reference
 * indent($str,$size=2) -- Add spaces at the start of each line, by default 2
 * unindent($str) -- Remove indentation equally from all lines until at least
 *                   one line starts at position 1
 * set_indents($str,$arr,$append=false) -- Set individual indentation for each line
 * get_indents($str) -- Get the number of leading WS chars for each line in a string
 *
 * @version 1.0
 * @copyright LGPL
 * @author Roger Baklund roger@baklund.no
 *
 *
 */
class Indentation {
  /** Concatinate indented line with the previous line
   *
   * Returns array of arrays with pairs of linenumbers and blocks.
   *
   * NOTE: Will also remove CR and blank lines in the process, but these lines are
   * still counted, the reported line numbers are correct according to the full
   * input. You can detect removed blank lines by checking the linenumber plus
   * the number of lines in a block and compare it with the linenumber in the
   * next block.
   *
   * @param string|array $raw_lines Multiline string or array of stringys
   * @return array Array of (Linenumber, Lines as a LF concatinated string)
   */
  static function blocks($raw_lines) {
    if(is_string($raw_lines))
      $raw_lines = explode("\n",str_replace("\r\n","\n",$raw_lines));
    $lines = array();
    $lineno = 0;
    foreach($raw_lines as $line) {
      $lineno = $lineno + 1;
      if(!strlen(trim($line))) continue; # skip blank lines, but accept "0"
      if($lines && in_array($line[0],array(' ',"\t"))) {
        $lines[count($lines)-1][1] .= "\n".rtrim($line); # append to last line
        continue;
      }
      $lines[] = array($lineno,rtrim($line));
    }
    return $lines;
  }
  /** Remove indentation
   *
   * Removes preceeding spaces equally from all lines until at least one line
   * starts at position 1
   *
   * For practical reasons, the first line is not considered!
   *
   * \code
   * Indentation::unindent('First
   *   Second
   *   Third');
   * Indentation::unindent(
   *  'First
   *   Second
   *   Third');
   * Indentation::unindent(
   *  'First
   *     Second
   *     Third');
   * \endcode
   *
   * The first two are identical, the linefeed before the ' does not matter.
   * First, Second and Third are considered to be three items on the same level.
   * The third example is less intuitive, but it is the same as the two above.
   * All indentation for Second and Third is removed in all cases.
   *
   * If you wanted to keep the indentation in this case, you would have to leave
   * the first line blank:
   *
   * \code
   * Indentation::unindent('
   * First
   *   Second
   *   Third');
   * Indentation::unindent('
   *     First
   *       Second
   *       Third');
   * \endcode
   *
   * In the last two examples, Second and Third are indentated and 'belongs'
   * to First, only the indentation for First and the initial blank line is
   * removed.
   *
   * Note that the problem descibed above only occurs when there is a single
   * item with indented lines, both of the below examples will see this as two
   * items; First and Forth:
   *
   * \code
   * Indentation::unindent('First
   *   Second
   *   Third
   * Fourth');
   * Indentation::unindent('
   *     First
   *       Second
   *       Third
   *     Fourth');
   * \endcode
   *
   * @param string $str A multiline string for which to reduce indentation
   * @return string The string unindented, some leading spaces removed
   */
  static function unindent($str) {
    $lines = explode("\n",$str);
    $first = array_shift($lines);
    $min = PHP_INT_MAX;
    foreach($lines as $l) { # find smallest indentation
      $ind = strlen($l) - strlen(ltrim($l));
      if($ind < $min)
        $min = $ind;
    }
    foreach($lines as $idx=>$l)
      $lines[$idx] = substr($l,$min);
    return trim($first."\n".implode("\n",$lines));
  }
  /** Indent a multiline string by prepending each line with WS characters
   *
   * By default it will inject space characters, but you can provide "\t" or
   * chr(9) as the third parameter if you want to indent with TAB. There is
   * however no special handling of TAB, each character counts as one.
   *
   * @param string $str The multiline string to indent
   * @param int $size The size of the indentation, default is 2
   * @param string $ws The whitespace character to use, default SPACE (ascii 32)
   * @return string The multiline string indented
   */
  static function indent($str,$size=2,$ws=' ') {
    $lines = explode("\n",$str);
    foreach($lines as $idx=>$l)
      $lines[$idx] = str_repeat($ws,$size).$l;
    return implode("\n",$lines);
  }
  /** Set individual indentation for each line
   *
   * This method is similar to indent(), except you provide an array of int
   * values specifying the indentation for each line in the multiline string.
   *
   * @param string $str A multiline string you want to indent
   * @param array $arr Array of integers, specific indentation for each line
   * @param bool $append Set to true if you want to append the given numbers
   *   to existing indentation, be default it will replace existing indentation.
   * @return string The multiline string indented
   */
  static function set_indents($str,$arr,$append=false) {
    $lines = explode("\n",$str);
    foreach($lines as $idx=>$l)
      $lines[$idx] = str_repeat(' ',$arr[$idx]).($append?$l:ltrim($l));
    return implode("\n",$lines);
  }
  /** Get the number of leading spaces for each line in a multiline string
   *
   * @param string $str A multiline string
   * @return array A list of integers, one for each line in the input
   */
  static function get_indents($str) {
    $res = array();
    foreach(explode("\n",$str) as $l)
      $res[] = strlen($l) - strlen(ltrim($l));
    return $res;
  }
}

?>