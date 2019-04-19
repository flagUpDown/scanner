<?php
namespace FlagUpDown;

class ScanFunction extends ScanFile
{
    public static $BEGIN = '(';
    public static $END   = ')';
    public static $COMMA = ',';

    protected $functionNameList;

    public function __construct(array $functionNameList)
    {
        $this->functionNameList = $functionNameList;
    }

    public function resetFunctionNameList(array $functionNameList)
    {
        $this->functionNameList = $functionNameList;
    }

    public function scan(string $rootPath, array $ignoreDirs = []) : array
    {
        $result   = [];
        $fileList = self::findFilesPHP($rootPath, $ignoreDirs);
        foreach ($fileList as $file) {
            $functionList = $this->extractFunctionInfo($file);
            if (!empty($functionList)) {
                $result[$file] = $functionList;
            }
        }
        return $result;
    }

    protected function extractFunctionInfo(string $file) : array
    {
        $result  = [];
        $subject = file_get_contents($file);
        $tokens  = token_get_all($subject);

        foreach ($this->functionNameList as $functionName) {
            $functionTokens = token_get_all('<?php ' . $functionName);
            array_shift($functionTokens);

            $params = $this->matchToken($functionTokens, $tokens);
            if (!empty($params)) {
                $result[$functionName] = $params;
            }
        }
        return $result;
    }

    protected function matchToken($functionTokens, $tokens)
    {
        $list                = [];
        $functionTokensCount = count($functionTokens);
        $matchedTokensCount  = 0;
        $buffer              = [];
        foreach ($tokens as $token) {
            if ($matchedTokensCount < $functionTokensCount) {
                if (self::tokensEqual($token, $functionTokens[$matchedTokensCount])) {
                    ++$matchedTokensCount;
                } else {
                    $matchedTokensCount = 0;
                }
            } elseif ($matchedTokensCount === $functionTokensCount) {
                if (self::tokensEqual(self::$END, $token)) {
                    $list[] = $this->matchParams($buffer);

                    $matchedTokensCount = 0;
                    $buffer             = [];
                } elseif ($token !== self::$BEGIN && isset($token[0]) && !in_array($token[0], [T_WHITESPACE, T_COMMENT])) {
                    $buffer[] = $token;
                }
            }
        }
        return $list;
    }

    protected function matchParams($buffer)
    {
        $params  = [];
        $current = '';
        foreach ($buffer as $token) {
            if ($token === self::$COMMA) {
                $params[] = trim($current, ' ');
                $current  = '';
                continue;
            }
            if (isset($token['1'])) {
                $current .= $token['1'] . ' ';
            }
            if (is_string($token)) {
                $current .= $token . ' ';
            }
        }
        $params[] = trim($current, ' ');
        return $params;
    }
}
