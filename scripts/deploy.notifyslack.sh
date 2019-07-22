#!/bin/bash

# exit shell as soon as a command fails (exit status not 0):
set -o errexit
# Treat unset variables as an error when substituting:
set -o nounset

if [ ! -z "$SLACK_WEBHOOK_URL" ]; then

  REPO_URL="https://gitlab.com/foodsharing-dev/foodsharing"
  COMMIT_SHA=$(git rev-parse HEAD)
  COMMIT_SHA_SHORT=$(git rev-parse --short HEAD)
  COMMIT_MESSAGE=$(git log -1 --pretty="%s - %an")
  REF=$CI_COMMIT_REF_NAME;
  REF_URL="$REPO_URL/tree/$REF"
  COMMIT_URL="$REPO_URL/tree/$CI_COMMIT_SHA"

  URL="$CI_ENVIRONMENT_URL"
  DEPLOY_ENV="$CI_ENVIRONMENT_NAME"
  DEPLOY_EMOJI=":bananadance:"

  GITLAB_JOB_URL="https://gitlab.com/foodsharing-dev/foodsharing/-/jobs/$CI_JOB_ID"

  ATTACHMENT_TEXT=""

  ATTACHMENT_TEXT+=":foodsharing: <$URL|Visit the site>"

  ATTACHMENT_TEXT+="\n:gitlab: <$GITLAB_JOB_URL|Visit GitLab CI>"

  ATTACHMENT_FOOTER="Using git ref <$REF_URL|$REF>, commit <$COMMIT_URL|$COMMIT_SHA_SHORT> - $COMMIT_MESSAGE"

  payload=$(printf '{
      "channel": "#foodsharing-dev-git",
      "username": "deploy",
      "text": ":sparkles: Successful deployment of *foodsharing* to _%s_ %s",
      "attachments": [
        {
          "text": "%s",
          "footer": "%s"
        }
      ]
    }' "$DEPLOY_ENV" "$DEPLOY_EMOJI" "$ATTACHMENT_TEXT" "$ATTACHMENT_FOOTER")

  curl -X POST --data-urlencode "payload=$payload" "$SLACK_WEBHOOK_URL"

fi

