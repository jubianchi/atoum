#!/bin/bash

export TRAVIS_TOKEN=fZaDplMJBCCSMRu4pCChWg
export SHOU_BUILD_BODY="{ \
	\"request\": { \
		\"branch\": \"master\", \
		\"config\": { \
			\"env\": { \
				\"global\": { \
					\"SHOU_REPOSITORY_OWNER\": \"$(echo $TRAVIS_REPO_SLUG | cut -d'/' -f1)\", \
					\"SHOU_REPOSITORY_NAME\": \"$(echo $TRAVIS_REPO_SLUG | cut -d'/' -f2)\", \
					\"SHOU_PULL_REQUEST\": \"$(echo $TRAVIS_PULL_REQUEST)\" \
				} \
			} \
		} \
	} \
}"

echo $SHOU_BUILD_BODY

curl -v -s -X POST \
	-H "User-Agent: atoum" \
	-H "Content-Type: application/json" \
	-H "Accept: application/json" \
	-H "Travis-API-Version: 3" \
	-H "Authorization: token \"$TRAVIS_TOKEN\"" \
	-d "$SHOU_BUILD_BODY" \
	https://api.travis-ci.org/repo/atoum%2Fshou/requests
