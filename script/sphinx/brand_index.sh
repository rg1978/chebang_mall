#!/bin/bash

#需要修改以下三行数据 sphinx的安装目录、sphinx的配置目录、log的存放目录
sphinx_path="/usr/bin"
sphinx_config_path="/etc/sphinxsearch"
log_path="/usr/local/var/log"

"${sphinx_path}/indexer" syscategory_brand --config "${sphinx_config_path}/sphinx.conf" --rotate
