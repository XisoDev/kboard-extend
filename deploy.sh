#!/bin/bash

# 압축 파일 이름
OUTPUT="kboard-extend.zip"

# 기존 zip 파일 삭제
rm -f ${OUTPUT}

# 압축 명령 실행
zip -r ${OUTPUT} . \
    -x "./.git/*" \
    -x "./.git" \
    -x "./.idea/*" \
    -x "./.idea" \
    -x "./deploy.sh" \
    -x "./.gitignore"

echo "압축이 완료되었습니다: ${OUTPUT}"