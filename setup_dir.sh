#!/bin/bash
write_folders=images data/attach data/mailattach data/pass data/visite cache/searchindex css/gen js/gen tmp

for I in $write_folders; do
  mkdir -p "$I"
  chown 777 "$I"
done
