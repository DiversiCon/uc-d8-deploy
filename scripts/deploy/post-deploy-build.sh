#!/bin/bash

echo 'Post deploy build steps...'
rm deploy/docroot/.gitignore
cp docroot/themes/contrib/thunder_admin/css/components/breadcrumb.css deploy/docroot/themes/contrib/thunder_admin/css/components/breadcrumb.css
cp docroot/themes/contrib/thunder_admin/css/content-form-layout.css deploy/docroot/themes/contrib/thunder_admin/css/content-form-layout.css
echo 'Done with post deploy build steps.'
