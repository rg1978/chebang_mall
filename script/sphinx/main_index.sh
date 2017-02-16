#!/bin/bash
 
#需要修改sphinx的安装目录
sphinx_path="/usr/bin"

"${sphinx_path}/indexer" --all --rotate
 