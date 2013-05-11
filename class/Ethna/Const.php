<?php
/**
 * Ethna_Const
 * - preserves class static variable
 *
 * @author Yuki Matsukura <matsubokkuri@gmail.com>
 * @version 1.0
 */
/**
 * Ethna_Const
 * 
 */
class Ethna_Const
{


    /** �o�[�W������` */
    const ETHNA_VERSION =  '2.1.2-autoload';

    /** �N���C�A���g�����`: �p�� */
    const LANG_EN =  'en';

    /** �N���C�A���g�����`: ���{�� */
    const LANG_JA =  'ja';


    /** �Q�[�g�E�F�C: WWW */
    const GATEWAY_WWW =  1;

    /** �Q�[�g�E�F�C: CLI */
    const GATEWAY_CLI =  2;



    /** �v�f�^: ���� */
    const VAR_TYPE_INT =  1;

    /** �v�f�^: ���������_�� */
    const VAR_TYPE_FLOAT =  2;

    /** �v�f�^: ������ */
    const VAR_TYPE_STRING =  3;

    /** �v�f�^: ���t */
    const VAR_TYPE_DATETIME =  4;

    /** �v�f�^: �^�U�l */
    const VAR_TYPE_BOOLEAN =  5;

    /** �v�f�^: �t�@�C�� */
    const VAR_TYPE_FILE =  6;


    /** �t�H�[���^: text */
    const FORM_TYPE_TEXT =  1;

    /** �t�H�[���^: password */
    const FORM_TYPE_PASSWORD =  2;

    /** �t�H�[���^: textarea */
    const FORM_TYPE_TEXTAREA =  3;

    /** �t�H�[���^: select */
    const FORM_TYPE_SELECT =  4;

    /** �t�H�[���^: radio */
    const FORM_TYPE_RADIO =  5;

    /** �t�H�[���^: checkbox */
    const FORM_TYPE_CHECKBOX =  6;

    /** �t�H�[���^: button */
    const FORM_TYPE_SUBMIT =  7;

    /** �t�H�[���^: file */
    const FORM_TYPE_FILE =  8;

    /** �t�H�[���^: button */
    const FORM_TYPE_BUTTON =  9;

    /** �t�H�[���^: hidden */
    const FORM_TYPE_HIDDEN =  10;


    /** �G���[�R�[�h: ��ʃG���[ */
    const E_GENERAL =  1;


    /** �G���[�R�[�h: �Z�b�V�����G���[(�L�������؂�) */
    const E_SESSION_EXPIRE =  16;

    /** �G���[�R�[�h: �Z�b�V�����G���[(IP�A�h���X�`�F�b�N�G���[) */
    const E_SESSION_IPCHECK =  17;

    /** �G���[�R�[�h: �A�N�V��������`�G���[ */
    const E_APP_UNDEFINED_ACTION =  32;

    /** �G���[�R�[�h: �A�N�V�����N���X����`�G���[ */
    const E_APP_UNDEFINED_ACTIONCLASS =  33;

    /** �G���[�R�[�h: �A�v���P�[�V�����I�u�W�F�N�gID�d���G���[ */
    const E_APP_DUPENT =  34;

    /** �G���[�R�[�h: �A�v���P�[�V�������\�b�h�����݂��Ȃ� */
    const E_APP_NOMETHOD =  35;

    /** �G���[�R�[�h: ���b�N�G���[ */
    const E_APP_LOCK =  36;

    /** �G���[�R�[�h: CSV�����G���[(�s�p��) */
    const E_UTIL_CSV_CONTINUE =  64;

    /** �G���[�R�[�h: �t�H�[���l�^�G���[(�X�J���[�����ɔz��w��) */
    const E_FORM_WRONGTYPE_SCALAR =  128;

    /** �G���[�R�[�h: �t�H�[���l�^�G���[(�z������ɃX�J���[�w��) */
    const E_FORM_WRONGTYPE_ARRAY =  129;

    /** �G���[�R�[�h: �t�H�[���l�^�G���[(�����^) */
    const E_FORM_WRONGTYPE_INT =  130;

    /** �G���[�R�[�h: �t�H�[���l�^�G���[(���������_���^) */
    const E_FORM_WRONGTYPE_FLOAT =  131;

    /** �G���[�R�[�h: �t�H�[���l�^�G���[(���t�^) */
    const E_FORM_WRONGTYPE_DATETIME =  132;

    /** �G���[�R�[�h: �t�H�[���l�^�G���[(BOOL�^) */
    const E_FORM_WRONGTYPE_BOOLEAN =  133;

    /** �G���[�R�[�h: �t�H�[���l�^�G���[(FILE�^) */
    const E_FORM_WRONGTYPE_FILE =  134;

    /** �G���[�R�[�h: �t�H�[���l�K�{�G���[ */
    const E_FORM_REQUIRED =  135;

    /** �G���[�R�[�h: �t�H�[���l�ŏ��l�G���[(�����^) */
    const E_FORM_MIN_INT =  136;

    /** �G���[�R�[�h: �t�H�[���l�ŏ��l�G���[(���������_���^) */
    const E_FORM_MIN_FLOAT =  137;

    /** �G���[�R�[�h: �t�H�[���l�ŏ��l�G���[(������^) */
    const E_FORM_MIN_STRING =  138;

    /** �G���[�R�[�h: �t�H�[���l�ŏ��l�G���[(���t�^) */
    const E_FORM_MIN_DATETIME =  139;

    /** �G���[�R�[�h: �t�H�[���l�ŏ��l�G���[(�t�@�C���^) */
    const E_FORM_MIN_FILE =  140;

    /** �G���[�R�[�h: �t�H�[���l�ő�l�G���[(�����^) */
    const E_FORM_MAX_INT =  141;

    /** �G���[�R�[�h: �t�H�[���l�ő�l�G���[(���������_���^) */
    const E_FORM_MAX_FLOAT =  142;

    /** �G���[�R�[�h: �t�H�[���l�ő�l�G���[(������^) */
    const E_FORM_MAX_STRING =  143;

    /** �G���[�R�[�h: �t�H�[���l�ő�l�G���[(���t�^) */
    const E_FORM_MAX_DATETIME =  144;

    /** �G���[�R�[�h: �t�H�[���l�ő�l�G���[(�t�@�C���^) */
    const E_FORM_MAX_FILE =  145;

    /** �G���[�R�[�h: �t�H�[���l������(���K�\��)�G���[ */
    const E_FORM_REGEXP =  146;

    /** �G���[�R�[�h: �t�H�[���l���l(�J�X�^���`�F�b�N)�G���[ */
    const E_FORM_INVALIDVALUE =  147;

    /** �G���[�R�[�h: �t�H�[���l������(�J�X�^���`�F�b�N)�G���[ */
    const E_FORM_INVALIDCHAR =  148;

    /** �G���[�R�[�h: �m�F�p�G���g�����̓G���[ */
    const E_FORM_CONFIRM =  149;

    /** �G���[�R�[�h: �L���b�V���^�C�v�s�� */
    const E_CACHE_INVALID_TYPE =  256;

    /** �G���[�R�[�h: �L���b�V���l�Ȃ� */
    const E_CACHE_NO_VALUE =  257;

    /** �G���[�R�[�h: �L���b�V���L������ */
    const E_CACHE_EXPIRED =  258;

    /** �G���[�R�[�h: �L���b�V���G���[(���̑�) */
    const E_CACHE_GENERAL =  259;



}






