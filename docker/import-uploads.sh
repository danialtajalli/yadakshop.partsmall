#!/bin/sh
set -eu

# Seed the separate Docker volume without replacing uploads created in Docker.
cp -an /source-uploads/. /target-uploads/
chown -R www-data:www-data /target-uploads
printf 'Uploads imported; existing Docker files were preserved.\n'
