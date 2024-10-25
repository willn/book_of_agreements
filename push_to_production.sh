#!/bin/bash

# push all "public" files in the product to production
rsync -e 'ssh' -avz --exclude '*.swp' public/ gocoho@gocoho.org:/home/gocoho/public_html/boa/

#reset permissions
ssh -i ~/.ssh/id_dsa gocoho@gocoho.org 'cd ~/public_html/boa/ && ~/bin/fix_web_perms.sh';


