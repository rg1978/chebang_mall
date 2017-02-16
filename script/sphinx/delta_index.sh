#!/bin/bash

#需要修改以下三行数据 sphinx的安装目录、sphinx的配置目录、log的存放目录
sphinx_path="/usr/bin"
sphinx_config_path="/etc/sphinxsearch"
log_path="/usr/local/var/log"

"${sphinx_path}/indexer"  sysitem_item_delta --config "${sphinx_config_path}/sphinx.conf" --rotate
sleep 1
"${sphinx_path}/indexer" --merge sysitem_item_merge sysitem_item_delta --config "${sphinx_config_path}/sphinx.conf" --rotate >> "${log_path}/item_delta_index.log"
