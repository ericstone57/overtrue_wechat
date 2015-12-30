#!/bin/bash
#
TARGET_BRANCH="3.0"

mkdir .tmp
cd .tmp

delete_branch()
{
    TEMPDIR=$1

    mkdir -p $TEMPDIR

    git clone git@github.com:EasyWeChat/$1.git

    pushd $TEMPDIR

    git push origin :$TARGET_BRANCH

    pwd

    popd
}

delete_branch cache
delete_branch card
delete_branch core
delete_branch device
delete_branch encryption
delete_branch js
delete_branch material
delete_branch menu
delete_branch message
delete_branch notice
delete_branch payment
delete_branch qrcode
delete_branch semantic
delete_branch server
delete_branch staff
delete_branch stats
delete_branch store
delete_branch poi
delete_branch support
delete_branch url
delete_branch user

rm -rf ./.tmp