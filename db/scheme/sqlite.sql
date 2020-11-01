

DROP TABLE IF EXISTS cnt_document;

CREATE TABLE cnt_document (
  id integer PRIMARY KEY NOT NULL,
  name text,
  file_ext text,
  file_body blob,
  updated integer,
  workspace_id integer
);


DROP TABLE IF EXISTS cnt_link;

CREATE TABLE cnt_link (
  id integer PRIMARY KEY NOT NULL,
  url_consultant text,
  name text,
  url_garant text,
  url_web text,
  page_id integer,
  workspace_id integer
);


DROP TABLE IF EXISTS cnt_node;

CREATE TABLE cnt_node (
  id integer PRIMARY KEY NOT NULL,
  type text,
  url_web text,
  url_consultant text,
  url_garant text,
  workspace_id integer
);


DROP TABLE IF EXISTS cnt_page;

CREATE TABLE cnt_page (
  id integer PRIMARY KEY NOT NULL,
  path text,
  title text,
  content text,
  keywords text,
  description text,
  created datetime,
  trash integer,
  frame text,
  frame_data text,
  path_parts text,
  path_parent text,
  path_name text,
  workspace_id integer
);



DROP TABLE IF EXISTS dct_action;

CREATE TABLE dct_action (
  id integer PRIMARY KEY NOT NULL,
  header text,
  type text,
  settings text,
  updated integer,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_algorithm;

CREATE TABLE dct_algorithm (
  id integer PRIMARY KEY NOT NULL,
  name text,
  screen_grp boolean,
  workspace_id integer,
  has_map boolean
);


DROP TABLE IF EXISTS dct_algorithm_answer_map;

CREATE TABLE dct_algorithm_answer_map (
  id integer PRIMARY KEY NOT NULL,
  algorithm_id integer,
  dst_type text,
  from_id integer,
  to_id integer,
  context_id integer,
  hide boolean,
  fields character varying,
  answers character varying,
  multiple boolean,
  workspace_id integer,
  src_type text
);


DROP TABLE IF EXISTS dct_answer;

CREATE TABLE dct_answer (
  id integer PRIMARY KEY NOT NULL,
  question_id integer,
  header character varying,
  idx integer,
  excl boolean,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_answer_context;

CREATE TABLE dct_answer_context (
  id integer PRIMARY KEY NOT NULL,
  object_id integer,
  context_id integer,
  text text,
  not_exists integer,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_conclusion;

CREATE TABLE dct_conclusion (
  id integer PRIMARY KEY NOT NULL,
  header text,
  type text,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_conclusion_file;

CREATE TABLE dct_conclusion_file (
  id integer PRIMARY KEY NOT NULL,
  conclusion_id integer,
  body text,
  updated integer,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_conclusion_context;

CREATE TABLE dct_conclusion_context (
  id integer PRIMARY KEY NOT NULL,
  object_id integer,
  context_id integer,
  text text,
  not_exists integer,
  workspace_id integer,
  updated integer
);


DROP TABLE IF EXISTS dct_context;

CREATE TABLE dct_context (
  id integer PRIMARY KEY NOT NULL,
  name text,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_document;

CREATE TABLE dct_document (
  id integer PRIMARY KEY NOT NULL,
  header text,
  document_general_id integer,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_document_context;

CREATE TABLE dct_document_context (
  id integer PRIMARY KEY NOT NULL,
  object_id integer,
  context_id integer,
  text text,
  not_exists integer,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_document_general;

CREATE TABLE dct_document_general (
  id integer PRIMARY KEY NOT NULL,
  header text,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_document_general_context;

CREATE TABLE dct_document_general_context (
  id integer PRIMARY KEY NOT NULL,
  object_id integer,
  context_id integer,
  text text,
  not_exists integer,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_expression;

CREATE TABLE dct_expression (
  id integer PRIMARY KEY NOT NULL,
  header text,
  formula character varying,
  type character varying,
  workspace_id integer,
  items text
);


DROP TABLE IF EXISTS dct_expression_answer;

CREATE TABLE dct_expression_answer (
  id integer PRIMARY KEY NOT NULL,
  expression_id integer,
  header character varying,
  condition character varying,
  formula character varying,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_expression_context;

CREATE TABLE dct_expression_context (
  id integer PRIMARY KEY NOT NULL,
  object_id integer,
  context_id integer,
  text text,
  not_exists integer,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_info;

CREATE TABLE dct_info (
  id integer PRIMARY KEY NOT NULL,
  header text,
  type text,
  default_answer_id integer,
  workspace_id integer,
  default_value character varying,
  settings text,
  placeholder character varying,
  required integer
);


DROP TABLE IF EXISTS dct_info_context;

CREATE TABLE dct_info_context (
  id integer PRIMARY KEY NOT NULL,
  object_id integer,
  context_id integer,
  text text,
  not_exists integer,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_question;

CREATE TABLE dct_question (
  id integer PRIMARY KEY NOT NULL,
  header text,
  type text,
  document_id integer,
  sngl_answer boolean,
  document_general_id integer,
  default_answer_id integer,
  inverted boolean,
  settings text,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_question_context;

CREATE TABLE dct_question_context (
  id integer PRIMARY KEY NOT NULL,
  object_id integer,
  context_id integer,
  text text,
  not_exists integer,
  workspace_id integer
);



DROP TABLE IF EXISTS dct_rel_algorithm_context;

CREATE TABLE dct_rel_algorithm_context (
  id integer PRIMARY KEY NOT NULL,
  algorithm_id integer,
  context_id integer,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_rel_expression_entity;

CREATE TABLE dct_rel_expression_entity (
  id integer PRIMARY KEY NOT NULL,
  object_id integer,
  entity_type integer,
  entity_id integer,
  idx integer,
  default_value character varying,
  workspace_id integer
);


DROP TABLE IF EXISTS dct_risk;

CREATE TABLE dct_risk (
  id integer PRIMARY KEY NOT NULL,
  header text,
  document_id integer,
  risk_general_id integer,
  document_general_id integer,
  workspace_id integer
);



DROP TABLE IF EXISTS dct_risk_context;

CREATE TABLE dct_risk_context (
  id integer PRIMARY KEY NOT NULL,
  object_id integer,
  context_id integer,
  text text,
  not_exists integer,
  workspace_id integer,
  updated integer
);



DROP TABLE IF EXISTS dct_risk_general;

CREATE TABLE dct_risk_general (
  id integer PRIMARY KEY NOT NULL,
  header text,
  level integer,
  workspace_id integer
);



DROP TABLE IF EXISTS dct_risk_general_context;

CREATE TABLE dct_risk_general_context (
  id integer PRIMARY KEY NOT NULL,
  object_id integer,
  context_id integer,
  text text,
  not_exists integer,
  workspace_id integer
);


DROP TABLE IF EXISTS lgc_link;

CREATE TABLE lgc_link (
  id integer PRIMARY KEY NOT NULL,
  parent_id integer,
  child_id integer,
  answer_id integer,
  algorithm_id integer,
  workspace_id integer
);


DROP TABLE IF EXISTS lgc_node_action;

CREATE TABLE lgc_node_action (
  id integer PRIMARY KEY NOT NULL,
  algorithm_id integer,
  action_id integer,
  workspace_id integer
);


DROP TABLE IF EXISTS lgc_node_algorithm;

CREATE TABLE lgc_node_algorithm (
  id integer PRIMARY KEY NOT NULL,
  algorithm_id integer,
  alg_id integer,
  workspace_id integer
);


DROP TABLE IF EXISTS lgc_node_conclusion;

CREATE TABLE lgc_node_conclusion (
  id integer PRIMARY KEY NOT NULL,
  algorithm_id integer,
  conclusion_id integer,
  workspace_id integer
);


DROP TABLE IF EXISTS lgc_node_end_point;

CREATE TABLE lgc_node_end_point (
  id integer PRIMARY KEY NOT NULL,
  algorithm_id integer,
  workspace_id integer
);


DROP TABLE IF EXISTS lgc_node_expression;

CREATE TABLE lgc_node_expression (
  id integer PRIMARY KEY NOT NULL,
  algorithm_id integer,
  expression_id integer,
  comment text,
  workspace_id integer
);


DROP TABLE IF EXISTS lgc_node_info;

CREATE TABLE lgc_node_info (
  id integer PRIMARY KEY NOT NULL,
  algorithm_id integer,
  info_id integer,
  workspace_id integer
);


DROP TABLE IF EXISTS lgc_node_question;

CREATE TABLE lgc_node_question (
  id integer PRIMARY KEY NOT NULL,
  algorithm_id integer,
  question_id integer,
  end_node_id integer,
  comment text,
  workspace_id integer
);


DROP TABLE IF EXISTS lgc_node_question_end;

CREATE TABLE lgc_node_question_end (
  id integer PRIMARY KEY NOT NULL,
  algorithm_id integer,
  question_id integer,
  question_node_id integer,
  workspace_id integer
);


DROP TABLE IF EXISTS lgc_node_risk;

CREATE TABLE lgc_node_risk (
  id integer PRIMARY KEY NOT NULL,
  algorithm_id integer,
  risk_id integer,
  workspace_id integer
);


DROP TABLE IF EXISTS lgc_node_start_point;

CREATE TABLE lgc_node_start_point (
  id integer PRIMARY KEY NOT NULL,
  algorithm_id integer,
  workspace_id integer
);



CREATE INDEX idx_dct_algorithm_answer_map_alg_id ON dct_algorithm_answer_map (algorithm_id);



CREATE INDEX idx_lgc_link_alg_id ON lgc_link (algorithm_id);

CREATE INDEX idx_lgc_node_algorithm_alg_id ON lgc_node_algorithm (algorithm_id);

CREATE INDEX idx_lgc_node_algorithm_object_id ON lgc_node_algorithm (alg_id);

CREATE INDEX idx_lgc_node_conclusion_alg_id ON lgc_node_conclusion (algorithm_id);

CREATE INDEX idx_lgc_node_conclusion_object_id ON lgc_node_conclusion (conclusion_id);

CREATE INDEX idx_lgc_node_end_point_alg_id ON lgc_node_end_point (algorithm_id);

CREATE INDEX idx_lgc_node_expression_alg_id ON lgc_node_expression (algorithm_id);

CREATE INDEX idx_lgc_node_expression_object_id ON lgc_node_expression (expression_id);

CREATE INDEX idx_lgc_node_info_alg_id ON lgc_node_info (algorithm_id);

CREATE INDEX idx_lgc_node_info_object_id ON lgc_node_info (info_id);

CREATE INDEX idx_lgc_node_question_alg_id ON lgc_node_question (algorithm_id);

CREATE INDEX idx_lgc_node_question_end_alg_id ON lgc_node_question_end (algorithm_id);

CREATE INDEX idx_lgc_node_question_end_object_id ON lgc_node_question_end (question_id);

CREATE INDEX idx_lgc_node_question_object_id ON lgc_node_question (question_id);

CREATE INDEX idx_lgc_node_risk_alg_id ON lgc_node_risk (algorithm_id);

CREATE INDEX idx_lgc_node_risk_object_id ON lgc_node_risk (risk_id);

CREATE UNIQUE INDEX idx_lgc_node_start_point_alg_id ON lgc_node_start_point (algorithm_id);



CREATE INDEX idx_dct_answer_qst_id ON dct_answer (question_id);

CREATE INDEX idxdct_rel_algorithm_context_alg_id ON dct_rel_algorithm_context (algorithm_id);



CREATE INDEX idx_dct_answer_context_ob_id ON dct_answer_context (object_id);

CREATE INDEX idx_dct_conclusion_context_ob_id ON dct_conclusion_context (object_id);

CREATE INDEX idx_dct_document_context_ob_id ON dct_document_context (object_id);

CREATE INDEX idx_dct_document_general_context_ob_id ON dct_document_general_context (object_id);

CREATE INDEX idx_dct_expression_context_ob_id ON dct_expression_context (object_id);

CREATE INDEX idx_dct_info_context_ob_id ON dct_info_context (object_id);

CREATE INDEX idx_dct_question_context_ob_id ON dct_question_context (object_id);

CREATE INDEX idx_dct_risk_context_ob_id ON dct_risk_context (object_id);

CREATE INDEX idx_dct_risk_general_context_ob_id ON dct_risk_general_context (object_id);


CREATE INDEX idx_dct_conclusion_file_cn_id ON dct_conclusion_file (conclusion_id);




