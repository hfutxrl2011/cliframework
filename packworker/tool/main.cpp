/***************************************************************************
 *
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * $Id: main.cpp, v1.0.0, 2015-07-29 10:33:03, mawentao(mawt@youzu.com) Exp $
 *
 **************************************************************************/

/**
 * @file   main.cpp
 * @author mawentao(mawt@youzu.com)
 * @date   2015-07-29 10:33:03
 * @brief  ���������
 **/

#include <cstdio>
#include <cstdlib>
#include <iostream>
#include <unistd.h>
using namespace std;


/** ���������в�������ȡ���ò���ʼ�� */
int parse_args(int argc, char *argv[])
{
    if (argc<2) {
        cout<<"usage: "<<argv[0]<<" '[cmd]'"<<endl;
        exit(0);
    }

    return 0;
}


/** ������ */
int main(int argc, char *argv[])
{

    //1. ���������в�������ȡ���ò���ʼ��
    int ret = parse_args(argc, argv);
    if(0 != ret){
        printf("parse_args fail.");
    }

    char *cmd = argv[1];
    while(1) {
        system(cmd);
        sleep(1);
    }

    cout<<"!!!THE END!!!"<<endl;
    return 0;
}


// vim:fdm=marker:nu:ts=4:sw=4:expandtab
