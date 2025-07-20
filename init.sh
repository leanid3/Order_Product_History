#!/bin/bash

USER_ID=$(id -u)
GROUP_ID=$(id -g)

ENV_FILE=$(pwd)/.env

# add_env_to_file $type
# $type: user, group, all
add_env_to_file($type){

    case $type in
        "user")
            echo "USER_ID=$USER_ID" >> $ENV_FILE
            ;;
        "group")
            echo "GROUP_ID=$GROUP_ID" >> $ENV_FILE
            ;;
        "all")
            echo "USER_ID=$USER_ID" >> $ENV_FILE
            echo "GROUP_ID=$GROUP_ID" >> $ENV_FILE
            ;;
    esac
}

if [ ! -f $ENV_FILE ]; then
    touch "$ENV_FILE"
    add_env_to_file "all"
else
    UID_EXISTS=$(grep -E '^USER_ID=' "$ENV_FILE")
    GID_EXISTS=$(grep -E '^GROUP_ID=' "$ENV_FILE")

    if [ -n "$UID_EXISTS" ] && [ -n "$GID_EXISTS" ]; then
        echo "USER_ID and GROUP_ID already exist in .env file"
    elif [ -n "$UID_EXISTS" ] && [ -z "$GID_EXISTS" ]; then
        add_env_to_file "user"
    elif [ -z "$UID_EXISTS" ] && [ -n "$GID_EXISTS" ]; then
        add_env_to_file "group"
    elif [ -z "$UID_EXISTS" ] && [ -z "$GID_EXISTS" ]; then
        add_env_to_file "all"
    fi
fi




