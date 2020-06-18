#!/bin/bash
cd "$(dirname "$0")"

# run bundle install in this dir
bundle install --path=vendor

# compile sass, provide optional argument (ie: point to different config.rb)
bundle exec compass compile --force $1

