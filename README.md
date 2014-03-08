Indentation
-----------

A few methods for working with text indentation.

    blocks($raw_lines)   -- Input multiline string or array of strings, returns
                            array of blocks paired with line number reference
    indent($str,$size=2) -- Add spaces at the start of each line, by default 2
    unindent($str)       -- Remove indentation equally from all lines until at least
                            one line starts at position 1
    get_indents($str)    -- Get the number of leading WS chars for each line in a string
    set_indents($str,$arr,$append=false) -- Set individual indentation for each line


