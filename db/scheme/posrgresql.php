<?php

$tables = [
    [
        'table' => 'cnt_node',
        'columns' => ['id', 'type', 'url_web', 'url_consultant', 'url_garant', 'workspace_id'],
    ],

    [
        'table' => 'cnt_link',
        'columns' => ['id', 'url_consultant', 'name', 'url_garant', 'url_web', 'page_id', 'workspace_id'],
    ],

    [
        'table' => 'cnt_document',
        'columns' => ['id', 'name', 'file_ext', 'file_body', 'updated', 'workspace_id'],
    ],

    [
        'table' => 'cnt_page',
        'columns' => ['id', 'path', 'title', 'content', 'keywords', 'description', 'created', 'trash', 'frame', 'frame_data', 'path_parts', 'path_parent', 'path_name', 'workspace_id'],
    ],

    [
        'table' => 'dct_action',
        'columns' => ['id', 'header', 'type', 'settings', 'workspace_id'],
    ],

    [
        'table' => 'dct_question',
        'columns' => ['id', 'header', 'type', 'document_id', 'sngl_answer', 'document_general_id', 'default_answer_id', 'inverted', 'settings', 'workspace_id'],
    ],

    [
        'table' => 'dct_answer',
        'columns' => ['id', 'question_id', 'header', 'idx', 'excl', 'workspace_id'],
    ],

    [
        'table' => 'dct_answer_context',
        'columns' => ['id', 'object_id', 'context_id', 'text', 'not_exists', 'workspace_id'],
    ],

    [
        'table' => 'dct_conclusion_context',
        'columns' => ['id', 'object_id', 'context_id', 'text', 'not_exists', 'workspace_id', 'updated'],
    ],

    [
        'table' => 'dct_risk_context',
        'columns' => ['id', 'object_id', 'context_id', 'text', 'not_exists', 'workspace_id', 'updated'],
    ],

    [
        'table' => 'dct_rel_expression_entity',
        'columns' => ['id', 'object_id', 'entity_type', 'entity_id', 'idx', 'default_value', 'workspace_id'],
    ],

    [
        'table' => 'dct_expression_answer',
        'columns' => ['id', 'expression_id', 'header', 'condition', 'formula', 'workspace_id'],
    ],

    [
        'table' => 'dct_risk_general_context',
        'columns' => ['id', 'object_id', 'context_id', 'text', 'not_exists', 'workspace_id'],
    ],

    [
        'table' => 'dct_document_context',
        'columns' => ['id', 'object_id', 'context_id', 'text', 'not_exists', 'workspace_id'],
    ],

    [
        'table' => 'dct_rel_algorithm_context',
        'columns' => ['id', 'algorithm_id', 'context_id', 'workspace_id'],
    ],

    [
        'table' => 'dct_risk',
        'columns' => ['id', 'header', 'document_id', 'risk_general_id', 'document_general_id', 'workspace_id'],
    ],

    [
        'table' => 'dct_document_general_context',
        'columns' => ['id', 'object_id', 'context_id', 'text', 'not_exists', 'workspace_id'],
    ],

    [
        'table' => 'dct_conclusion',
        'columns' => ['id', 'header', 'type', 'workspace_id'],
    ],

    [
        'table' => 'dct_conclusion_file',
        'columns' => ['id', 'conclusion_id', 'body', 'updated', 'workspace_id'],
    ],

    [
        'table' => 'dct_document',
        'columns' => ['id', 'header', 'document_general_id', 'workspace_id'],
    ],

    [
        'table' => 'dct_context',
        'columns' => ['id', 'name', 'workspace_id'],
    ],

    [
        'table' => 'dct_document_general',
        'columns' => ['id', 'header', 'workspace_id'],
    ],

    [
        'table' => 'dct_expression',
        'columns' => ['id', 'header', 'formula', 'type', 'workspace_id', 'items'],
    ],

    [
        'table' => 'dct_info',
        'columns' => ['id', 'header', 'type', 'default_answer_id', 'workspace_id', 'default_value', 'settings', 'placeholder', 'required'],
    ],

    [
        'table' => 'dct_risk_general',
        'columns' => ['id', 'header', 'level', 'workspace_id'],
    ],

    [
        'table' => 'lgc_node_action',
        'columns' => ['id', 'algorithm_id', 'action_id', 'workspace_id'],
    ],

    [
        'table' => 'lgc_node_start_point',
        'columns' => ['id', 'algorithm_id', 'workspace_id'],
    ],

    [
        'table' => 'lgc_node_risk',
        'columns' => ['id', 'algorithm_id', 'risk_id', 'workspace_id'],
    ],

    [
        'table' => 'lgc_node_question_end',
        'columns' => ['id', 'algorithm_id', 'question_id', 'question_node_id', 'workspace_id'],
    ],

    [
        'table' => 'lgc_node_question',
        'columns' => ['id', 'algorithm_id', 'question_id', 'end_node_id', 'comment', 'workspace_id'],
    ],

    [
        'table' => 'lgc_node_info',
        'columns' => ['id', 'algorithm_id', 'info_id', 'workspace_id'],
    ],

    [
        'table' => 'lgc_node_expression',
        'columns' => ['id', 'algorithm_id', 'expression_id', 'comment', 'workspace_id'],
    ],

    [
        'table' => 'lgc_node_end_point',
        'columns' => ['id', 'algorithm_id', 'workspace_id'],
    ],

    [
        'table' => 'lgc_node_conclusion',
        'columns' => ['id', 'algorithm_id', 'conclusion_id', 'workspace_id'],
    ],

    [
        'table' => 'lgc_node_algorithm',
        'columns' => ['id', 'algorithm_id', 'alg_id', 'workspace_id'],
    ],

    [
        'table' => 'lgc_link',
        'columns' => ['id', 'parent_id', 'child_id', 'answer_id', 'algorithm_id', 'workspace_id'],
    ],

    [
        'table' => 'dct_expression_context',
        'columns' => ['id', 'object_id', 'context_id', 'text', 'not_exists', 'workspace_id'],
    ],

    [
        'table' => 'dct_algorithm',
        'columns' => ['id', 'name', 'screen_grp', 'workspace_id', 'has_map'],
    ],

    [
        'table' => 'dct_algorithm_answer_map',
        'columns' => ['id', 'algorithm_id', 'dst_type', 'from_id', 'to_id', 'context_id', 'hide', 'fields', 'answers', 'multiple', 'workspace_id', 'src_type'],
    ],

    [
        'table' => 'dct_info_context',
        'columns' => ['id', 'object_id', 'context_id', 'text', 'not_exists', 'workspace_id'],
    ],

    [
        'table' => 'dct_question_context',
        'columns' => ['id', 'object_id', 'context_id', 'text', 'not_exists', 'workspace_id'],
    ],

];

return $tables;