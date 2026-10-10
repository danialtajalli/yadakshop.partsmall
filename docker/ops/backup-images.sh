#!/bin/bash
set -euo pipefail
docker image save partsmall-app:production partsmall-web:production mysql:8.0 | gzip -1
