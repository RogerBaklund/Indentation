Indentation
===========

A few methods for working with text indentation.

    blocks($raw_lines)           -- Input multiline string or array of strings, returns
                                    array of blocks paired with line number reference
    indent($str,$size=2,$ws=' ') -- Add spaces at the start of each line, by default 2
    unindent($str)               -- Remove indentation equally from all lines until at 
                                    least one line starts at position 1
    get_indents($str)            -- Get the number of leading WS characters for each line 
                                    in a multiline string
    set_indents($str,$arr,$append=false) -- Set individual indentation for each line

Each method is described in more detail below.

blocks()
--------

Splits a string into blocks based on indentation.

~~~~~{.php}
function blocks($raw_lines)
~~~~~

This method takes a single argument, a string or an array of strings, like output 
from `file()`. The string is split into lines, the indentation of each line is 
considered, and if it is indented compared to a previous line, it is considered
to "belong" to that previous line, the two (or more) forms a "block". Empty lines
are ignored. Note that the lines are *not* made into a single line, the `LF` is 
preserved, and the indentation is *not* removed. This means you can apply `blocks()` 
to it's own output to read multiple levels of indentation.

The `blocks()` method returns an array of arrays. The inner array has two items: 
the line number from the input source as an integer, and the block as a single string.

~~~~~{.php}
$blocks = Indentation::blocks(file('data.txt'));
$blocks = Indentation::blocks(file_get_contents('data.txt'));
$blocks = Indentation::blocks($LinesArray);
$blocks = Indentation::blocks($String);
~~~~~

Note that using constant multiline strings in the PHP source code requires zero
indentation for the root items, otherwise it would be seen as a single multiline
block. This results in ugly source code:

~~~~~{.php}
class foo {
  function bar() {
    if($somthing) {
      $blocks = Indentation::blocks(
        "First
           indented
Second
  indented
Third
  indented");
      foreach($blocks as $chunk) {
        list($LineNo,$block) = $chunk;
        Process($LineNo,$block);
      }
    }
  }
}
~~~~~

Use the `unindent()` method to avoid this problem:

~~~~~{.php}
$blocks = Indentation::blocks(
          Indentation::unindent(
   "First
      indented
    Second
      indented
    Third
      indented"));
foreach($blocks as $chunk) ...
~~~~~


indent()
--------

Injects a number of WS characters at the start of each line in the string.

~~~~~{.php}
function indent($str,$size=2,$ws=' ')
~~~~~

This method takes one, two or three arguments. The first is required, it is a 
multiline string. The second is optional, the number of whitespace characters 
to inject, the default value is 2. The third argument is also optional, it is 
which whitespace character to use, by default it will be a space. You could 
provide `"\t"` or chr(9) if you wanted to inject TABs, or you could use 
`"&nbsp;"` for HTML output.


unindent()
----------

Remove excessive indentation.

~~~~~{.php}
function unindent($str)
~~~~~

This method takes a single argument, a multiline string, and removes excessive 
whitespace from the start of each line. The first line is a special case: any 
indentation is removed and ignored. The first line with characters is allways 
considered to be at the "root level" of the collection of "blocks". 

### What is excessive indentation? ###

If *all* *lines* in the input except the first have `X` spaces or more at the 
start of the line, then `X` spaces will be removed from all lines, so that at 
least one (the first) line will start at position one on the line.

For example, this:

~~~php
Indentation::unindent("
          First 
            indented
          Second
            indented");               
# the lines above has 9 and 11 characters indentation
~~~

...results in this:

    First
      idented
    Second
      indented

Note that the structure is kept, the entire block of text is shifted to the left.
Lines First and Second has no indentation, lines two and four ("indented") are 
still indented, but now with only two space characters.

Sometimes the structure *is* changed, but only for the first line. Any leading 
whitespace will be removed and ignored when considering the indentation levels.

For example, this:

        First 
      indented
    Second
      indented
      
...is converted into this:

    First 
      indented
    Second
      indented

The difference is only in the indentation for the first line, which is handled 
in this special way for practical reasons. 

The `blocks()` method is used to split a string into blocks based on the 
indentation. There is a special case when there is only one such block.

Suppose you wanted Second and Third to be indented below First. This will not 
work:

~~~~~{.php}
Indentation::unindent('First
  Second
  Third');
Indentation::unindent(
  'First
   Second
   Third');
Indentation::unindent(
  'First
     Second
     Third');
~~~~~

The first two are identical, the linefeed *before* the ' does not matter. First, 
Second and Third are considered to be three items on the same level. The third 
example is less intuitive, the indentation is increased for Second and Third, but 
it is the same as the two above. All indentation for Second and Third is removed 
in all cases.

Even this next example is the same as the above, because the indentation for the 
first line is ignored:

~~~~~{.php}
Indentation::unindent('
    First
  Second
  Third');
~~~~~

If you wanted to keep the indentation in this case, you would have to leave
the first line blank *and* Second and Third must have more indentation than
First:
   
~~~~~{.php}
Indentation::unindent('
First
  Second
  Third');
Indentation::unindent('
    First
      Second
      Third');
~~~~~

In the last two examples, Second and Third are indentated and 'belongs' to First, 
only the indentation for First and the initial blank line is removed.

Note that the problem descibed above only occurs when there is a single item with 
indented lines, both of the below examples will see this as two blocks; First with
Second and Third indented, and Fourth:

~~~~~{.php}
Indentation::unindent('First
  Second
  Third
Fourth');
Indentation::unindent('
  First
    Second
    Third
  Fourth');
~~~~~

Even the next example represents the same two blocks, because indentation for the 
first line is **ignored:** 

~~~~~{.php}
Indentation::unindent('
      First
    Second
    Thirds
  Fourth');
~~~~~

get_indents()
-------------

Get indentation count for each line in a string with multipe lines.

~~~~~{.php}
function get_indents($str)
~~~~~

This method takes a single argument; the string to analyze. It will return an
array of integers. Length of array corresponds to number of lines in the input,
and each value represents the number of leading whitespace characters used for 
that line.

**Note:** TAB characters counts as one, just like space characters. If they are 
mixed the numbers does not reflect the indentation you can see in an editor.

**Note:** In addition to TAB and space characters, ascii 0, 11 and 13 will also
count as one.
    
set_indents()
-------------

Set indentation on individual lines in a multiline string.

~~~~~{.php}
function set_indents($str,$arr,$append=false) 
~~~~~

This method takes one, two or three arguments. The first two are required. 
The first is a multiline string, the string for which you want to change 
the indentation. The second is an array of integers, each represents the 
amount of whitespace you wish to set on each line. The third, optional 
argument decides if the whitespace should be appended to existing whitespace
in the input sting, or if they represent the final indentation we want. The 
latter is the default, set it to `true` if you want it to append.


Examples
--------

Example using `unindent()` and `blocks()`:

~~~~~{.php}
$str = "
      This is a test                    
        string spanning 
        multiple lines.
      It contains three 
        sentences, each are 
        split on three lines.
      The indentation decides 
        which lines belongs 
          to which sentence.";
$str = Indentation::unindent($str);
echo '<pre>'.$str.'</pre>';
foreach(Indentation::blocks($str) as $idx => $block) {
  list($LineNo,$Sentence) = $block;
  echo '<p>'.
       'Sentence '.($idx+1).' from line '.$LineNo.': '.
       htmlentities($Sentence).
       '</p>';
}
~~~~~
    
This would output something like this:

    This is a                     
      string spanning 
      multiple lines.
    It contains three 
      sentences, each are 
      split on three lines.
    The indentation decides 
      which lines belongs 
        to which sentence.
        
    Sentence 1 from line 1: This is a string spanning multiple lines.

    Sentence 2 from line 4: It contains three sentences, each are split on three lines.

    Sentence 3 from line 7: The indentation decides which lines belongs to which sentence.
    
You can use the `get_indents()` method to analyze the indentation of a multiline 
string. 

~~~~~{.php}
$indents = Indentation::get_indents($String);
$lines = count($indents);
$smallest = min($indents);
$biggest = max($indents);
~~~~~

Empty lines in the input would be returned as 0 indented. you can not distinguish 
it from a line with text and no indentation without inspecting the line. The 
`blocks()` method automatically removes empty lines.

