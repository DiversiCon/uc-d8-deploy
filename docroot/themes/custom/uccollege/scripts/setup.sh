#!/usr/bin/env bash

cd "$(dirname "$0")" # change to current dir
cd ..

# install npm dependencies
npm install --no-optional
npm run dev
