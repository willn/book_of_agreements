#!/bin/bash

# push all "public" files in the product to dev space
rsync -e 'ssh' -avz --exclude '*.swp' public/ gocoho@gocoho.org:/home/gocoho/public_html/boa_dev/

#reset permissions
ssh -i ~/.ssh/id_dsa gocoho@gocoho.org 'cd ~/public_html/boa_dev/ && ~/bin/fix_web_perms.sh';


