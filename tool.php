<?php
#generates the AST/visitor classes from the definition

$file = fopen('ast.php', 'w');

fprintf($file, "<?php\nrequire_once('token.php');\n\n");

defineAst($file, 'Expr', [
	"Assign   : Token name, Expr value",
	"Binary   : Expr left, Token operator, Expr right",
	"Call     : Expr callee, Token paren, array arguments",
	"Get      : Expr object, Token name",
	"Grouping : Expr expression",
	"Literal  : value",
	"Logical  : Expr left, Token operator, Expr right",
	"Set      : Expr object, Token name, Expr value",
	"Super    : Token keyword, Token method",
	"This     : Token keyword",
	"Unary    : Token operator, Expr right",
	"Variable : Token name"
]);

defineAst($file, 'Stmt', [
	"Block      : array statements",
	"Class      : Token name, superclass, array methods",
	"Expression : Expr expression",
	"Function   : Token name, array parameters, array body",
	"If         : Expr condition, Stmt thenBranch, elseBranch",
	"Print      : Expr expression",
	"Return     : Token keyword, Expr value",
	"Var        : Token name, initializer",
	"While      : Expr condition, Stmt body"
]);

fclose($file);

function defineVisitor($file, $baseName, $types)
{
	fprintf($file, "interface Visitor%s\n{\n", $baseName);

	foreach ($types as $value)
	{
		$f = explode(':', $value);
		$className = trim($f[0]);

		fprintf($file, "\tpublic function visit%s%s(%s%s \$%s);\n", $className, $baseName, $className, $baseName, strtolower($baseName));
	}
	
	fprintf($file, "}\n\n");
}

function defineAst($file, $baseName, $types)
{
	fprintf($file, "abstract class %s\n{\n", $baseName);
	fprintf($file, "\tabstract public function accept(\$visitor);\n");
	fprintf($file, "}\n\n");

	defineVisitor($file, $baseName, $types);

	foreach ($types as $value)
	{
		$f = explode(':', $value);
		$className = trim($f[0]);
		$fields = trim($f[1]);

		defineType($file, $baseName, $className, $fields);
	}
}

function defineType($file, $baseName, $className, $fieldList)
{
	$className .= $baseName;
	fprintf($file, "class %s extends %s\n{\n", $className, $baseName);
	fprintf($file, "\tpublic function __construct(%s)\n\t{\n", add_dollar_signs($fieldList));
	$fields = explode(',', $fieldList);
	$fieldsAsMembers = '';
	foreach ($fields as $field)
	{
		$field = trim($field);
		if (strpos($field, ' ') != false)
		{
			$fieldname = trim(explode(' ', $field)[1]);
		}
		else
		{
			$fieldname = $field;
		}
		fprintf($file, "\t\t\$this->%s = \$%s;\n", $fieldname, $fieldname);
		$fieldsAsMembers .= sprintf("\tpublic \$%s;\n", $fieldname);
	}
	fprintf($file, "\t}\n\n");
	fprintf($file, "\tpublic function accept(\$visitor)\n\t{\n");
	fprintf($file, "\t\treturn \$visitor->visit%s(\$this);\n", $className);
	fprintf($file, "\t}\n\n");
	fprintf($file, $fieldsAsMembers);
	fprintf($file, "}\n\n");
}

function add_dollar_signs($typeList)
{
	$ret = [];
	$fields = explode(',', $typeList);
	foreach ($fields as $value)
	{
		$ret[] = add_dollar_sign(trim($value));
	}

	return implode(', ', $ret);
}

function add_dollar_sign($field)
{
	if (strpos($field, ' '))
	{
		return str_replace(' ', ' $', $field);
	}
	else
	{
		return '$'.$field;
	}
}
