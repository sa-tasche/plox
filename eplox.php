<?php
class EPlox
{
	public static $hadError = false;
	public static $hadRuntimeError = false;

	public static function error($line_or_token, $message)
	{
		if ($line_or_token instanceof Token)
		{
			$token = $line_or_token;
			if ($token->type == TOK_EOF)
			{
				self::report($token->line, " at end", $message);
			}
			else
			{
				self::report($token->line, " at '" . $token->literal . "'", $message);
			}
		}
		else
		{
			$line = $line_or_token;
			self::report($line, '', $message);
		}
	}

	public static function report($line, $where, $message)
	{
		print("[line $line] Error$where: $message\n");
		self::$hadError = true;
	}

	public static function runtimeError(RuntimeError $error)
	{
		print("[line " . $error->token->line . "] " . $error->getMessage() . "\n");
		self::$hadRuntimeError = true;
	}

	public static function warning($message)
	{
		print("WARNING: " . $message . "\n");
	}
}
