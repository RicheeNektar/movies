#!/bin/bash

tar --exclude='./series' --exclude='./movies' --exclude="*.env*" --exclude='*node_modules*' --exclude='*vendor*' --exclude='*var*' -jvcf src.tbz ./*

