#!/bin/sh
set -eu

trap 'rm -f public/hot' EXIT
trap 'exit 0' TERM INT
npm ci
npm run dev &
vite_pid=$!
wait "$vite_pid"
