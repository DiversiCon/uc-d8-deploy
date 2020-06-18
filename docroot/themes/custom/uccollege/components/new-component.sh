#!/usr/bin/env bash

E_ARGS=3  # Error code
red=`tput setaf 1`
green=`tput setaf 2`
NC=`tput sgr0` # no color

cd "$(dirname "$0")" # set to components dir

echo "Type the name of the component (lowercase), followed by [ENTER]:"

read name

if [ -d $name ]; then
  echo "'$name' already exists, please create a new component"
  exit $E_ARGS
else
  echo "Create '$name' component …"
  mkdir $name && cd $name
  cp ../starter/_c-starter.scss ./_c-$name.scss && sed "-i" "" "-e" "s/starter/$name/g" _c-$name.scss
  cp ../starter/README.md ./ && sed "-i" "" "-e" "s/starter/$name/g" README.md
  cp ../starter/starter.vue ./$name.vue && sed "-i" "" "-e" "s/starter/$name/g" $name.vue
  cp ../starter/starter.html.twig ./$name.html.twig && sed "-i" "" "-e" "s/starter/$name/g" $name.html.twig
  cp ../starter/showcase.yml ./ && sed "-i" "" "-e" "s/starter/$name/g" showcase.yml
  cp ../starter/showcase--starter.html.twig ./showcase--$name.html.twig && sed "-i" "" "-e" "s/starter/$name/g" showcase--$name.html.twig
  echo "${green}$name${NC} component created"
  echo "Remember to declare this ${green}$name${NC} component in ../src/js/main.js and include sass in ../src/sass/_components.scss"
fi
