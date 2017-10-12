RED='\033[0;31m'
NC='\033[0m'
vendor/bin/php-cs-fixer "$@"
if [ $? -ne 0 ]; then
  printf "${RED}Code style check failed. See problems in the following diff:${NC}\n"
  git -c core.fileMode=false diff --color
  exit 1
fi
exit 0
