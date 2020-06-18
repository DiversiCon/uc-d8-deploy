#!/usr/bin/env bash

cd "$(dirname "$0")" # change to current dir
cd ..

# compile theme's front-end assets
npm install --no-optional
npm run production
