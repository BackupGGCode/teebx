This document contains the coding guidelines for the TeeBX Project.
## Introduction ##
In order to keep the code consistent, contributors are pleased to use the following conventions.
It is not a judgment call on your coding abilities, but more of a style and look call. Please try to follow these guidelines to ensure prettiness.

**Basic rules:**

> It is more important to be correct than to be fast.<br>
<blockquote>It is more important to be maintainable than to be fast.<br>
Spending a minute now commenting code will save a lot tomorrow.<br>
Fast code that is difficult to maintain is likely going to be looked down upon.</blockquote>

<h3>Indentation:</h3>

- Use tabs, <b>NOT</b> spaces so anyone will be able to view indentation the way they like.<br>
But be careful to use consistently, restricted to logical indentation, please do not use tabs for alignment.<br>
<br>
- Bracing.<br>
Use Allman style, also referred to as "ANSI style".<br>
Indented code is clearly set apart from the containing statement by lines that are almost completely whitespace, improving readability, and the closing brace lines up in the same column as the opening brace, making it easy to find matching braces.<br>
<pre><code>while (x &lt; y)<br>
{<br>
  if (isset($this['index']))<br>
  {<br>
    $some .= $that . "$x\n";<br>
  {<br>
  $x++;<br>
}<br>
 <br>
finalThings();<br>
</code></pre>
When defining a function, use the C style for brace placement, that means, use a new line for the brace.<br>
<pre><code>function doSome($param)<br>
{<br>
  code<br>
  ...<br>
}<br>
</code></pre>
- Case statements.<br>
Switch statements must have the case one tab inner the switch, also the code block inside a case must be a tab inner the case.<br>
<pre><code>switch ($x)<br>
{<br>
  case 'a':<br>
    $result = $x;<br>
    break;<br>
  case 'b':<br>
    ...<br>
}<br>
</code></pre>

<h3>Whitespaces:</h3>
Use spaces to enhance readability.<br>
<pre><code>$location = 'tree';<br>
$format = 'There are now %d birds on the %s';<br>
for ($i = 1; $i &lt;= 10; $i++)<br>
{<br>
  printf($format, $i, $location);<br>
}<br>
</code></pre>

Place spaces between control statements and their parentheses.<br>
<pre><code>if ($x)<br>
{<br>
  i++;<br>
}<br>
</code></pre>
Do not place spaces between a function and its parentheses, or between a parenthesis and its content.<br>
<pre><code>$char = bin2hex(substr($str, $i, 1));<br>
$char = bin2hex (substr ($str, $i, 1)); // NO!!<br>
$char = bin2hex( substr( $str, $i, 1 ) ); // NO!!<br>
</code></pre>
<h3>Line breaking:</h3>
- Each statement should get its own line.<br>
<pre><code>x++; y++; // NO!!<br>
if ($a) echo 'abc'; // NO!!<br>
</code></pre>
This is ok...<br>
<pre><code>x++;<br>
y++;<br>
if ($a)<br>
  echo 'abc';<br>
</code></pre>
<h3>Operators:</h3>
Avoid using the "ternary operator" as a conditional operator whenever possible, it's concise but make code less readable and more prone to errors and unexpected results.<br>

This one...<br>
<pre><code>result = ($a&gt;$b)?$x:$y; // Please NO!!<br>
</code></pre>
Can be easily rewritten as...<br>
<pre><code>if ($a &gt; $b)<br>
{<br>
  $result = $x;<br>
}<br>
else<br>
{<br>
  $result = $y;<br>
}<br>
</code></pre>
<h3>Code Comments:</h3>
Comment every significant code block, so others would easily understand it's function.<br>
Every function and/or class method declaration <b>must be preceeded</b> by a comment clearly explaing it's purpose and calling parameters. PhpDocumentor format preferred.<br>
<pre><code>/**<br>
 * Shorthand to set options to be shown for an existing select html element.<br>
 *   The $items var sets one or more &lt;option&gt; tag using pipe separated list.<br>
 *   Each list element sets attributes for a specific option, separated by = sign:<br>
 *   value=display_value=selected_flag=option_group_label<br>
 *   the first two values are mandatory.<br>
 * Usage example:<br>
 *   $form-&gt;setSelectOpts('line', 'isdnbri=ISDN BRI=1=Technology|analog=POTS Line=0=Technology');<br>
 *<br>
 * @param string $id select tag unique identifier<br>
 * @param string $options pipe separated list of select options and attibutes<br>
 * @return integer (0: No errors, 10: id not found, 20: id not a select, 30: not enough attributes<br>
 */<br>
public function setSelectOpts($id, $options)<br>
{<br>
	if (!isset($this-&gt;tagsPool[$id]))<br>
</code></pre>
-