/*
 Navicat MySQL Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80028
 Source Host           : 127.0.0.1:3306
 Source Schema         : wei_admin_plus

 Target Server Type    : MySQL
 Target Server Version : 80028
 File Encoding         : 65001

 Date: 01/01/2024 00:08:58
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sys_admins
-- ----------------------------
DROP TABLE IF EXISTS `sys_admins`;
CREATE TABLE `sys_admins`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '管理员id',
  `parent_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级id，0为超管，有且只有一个',
  `account` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '账号，长度5-20，支持手机号',
  `nickname` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '头像相对路径',
  `pwd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码散列',
  `role_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色id',
  `is_able` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否启用；1是0否',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `account`(`account`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统管理员表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_admins
-- ----------------------------
INSERT INTO `sys_admins` VALUES (1, 0, 'admin', 'Administrator', NULL, '$2y$10$gXxRVcXUozxRZMPKR/degeXPeVTWOhPhRcLwHmq9FRf/aQ5Jc9Fx2', 1, 1, '2023-12-31 07:00:00', '2023-12-31 07:00:00');

-- ----------------------------
-- Table structure for sys_file_uploads
-- ----------------------------
DROP TABLE IF EXISTS `sys_file_uploads`;
CREATE TABLE `sys_file_uploads`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文件上传记录id',
  `admin_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员id',
  `original_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '原始文件名',
  `url` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件相对地址',
  `size` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小，单位：字节',
  `type` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件类型，1图片，2视频，3文档，4其他',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统文件上传记录表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for sys_login_logs
-- ----------------------------
DROP TABLE IF EXISTS `sys_login_logs`;
CREATE TABLE `sys_login_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '登录日志id',
  `admin_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员id',
  `ipv4` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '登录ipv4',
  `location` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '登录地点',
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '用户代理',
  `login_at` datetime NOT NULL COMMENT '登录时间',
  `logout_at` datetime DEFAULT NULL COMMENT '登出时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统登录日志表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for sys_menus
-- ----------------------------
DROP TABLE IF EXISTS `sys_menus`;
CREATE TABLE `sys_menus`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '菜单权限id',
  `parent_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级id',
  `title` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '菜单权限名称（含外链）',
  `menu_type` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '菜单类型；1普通菜单，2动态菜单，3接口，4外链',
  `auth_alias` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '权限别名，只能存在于menu_type=2|3',
  `page_path` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '前端页面路由（适用于vue-router4）',
  `dirname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件目录（适用于vue3）',
  `file_route_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文件名和路由名',
  `uri` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '资源地址（RESTful API）',
  `method` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '请求方式（GET,POST,PATCH,DELETE）',
  `icon` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '图标名称，使用icon-park图标',
  `sort` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '排序',
  `is_cache` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否开启前端页面缓存；1是0否',
  `is_able` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否启用；1是0否',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统菜单权限表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_menus
-- ----------------------------
INSERT INTO `sys_menus` VALUES (1, 0, '系统管理', 1, NULL, NULL, NULL, NULL, NULL, '', 'setting-one', 1, 0, 1);
INSERT INTO `sys_menus` VALUES (2, 1, '菜单管理', 1, NULL, 'system/menu', 'system', 'Menu', NULL, NULL, 'hamburger-button', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (3, 1, '角色管理', 1, NULL, 'system/role', 'system', 'Role', NULL, '', 'permissions', 2, 1, 1);
INSERT INTO `sys_menus` VALUES (4, 3, '列表', 3, NULL, NULL, NULL, NULL, 'sys/role', 'GET', NULL, 1, 0, 1);
INSERT INTO `sys_menus` VALUES (5, 3, '添加', 3, 'sys.role.add', NULL, NULL, NULL, 'sys/role', 'POST', NULL, 2, 0, 1);
INSERT INTO `sys_menus` VALUES (6, 3, '编辑', 3, 'sys.role.edit', NULL, NULL, NULL, 'sys/role', 'PATCH', NULL, 3, 0, 1);
INSERT INTO `sys_menus` VALUES (7, 3, '删除', 3, 'sys.role.delete', NULL, NULL, NULL, 'sys/role/\\d+', 'DELETE', NULL, 4, 0, 1);
INSERT INTO `sys_menus` VALUES (8, 3, '预览下级', 3, 'sys.role.view.children', NULL, NULL, NULL, 'sys/role/view/\\d+', 'GET', NULL, 5, 0, 1);
INSERT INTO `sys_menus` VALUES (9, 3, '权限分配', 2, 'sys.role.auth.assign', 'system/role/permission/:role_id(\\d+)', 'system', 'PermissionAssign', NULL, '', NULL, 6, 0, 1);
INSERT INTO `sys_menus` VALUES (10, 9, '权限预览', 3, NULL, NULL, NULL, NULL, 'sys/role/permission/\\d+', 'GET', NULL, 1, 0, 1);
INSERT INTO `sys_menus` VALUES (11, 9, '提交权限分配', 3, NULL, NULL, NULL, NULL, 'sys/role/permission', 'POST', NULL, 2, 0, 1);
INSERT INTO `sys_menus` VALUES (12, 1, '管理员管理', 1, '', 'system/admin', 'system', 'Admin', '', '', 'people', 3, 1, 1);
INSERT INTO `sys_menus` VALUES (13, 12, '列表', 3, '', '', '', '', 'sys/admin', 'GET', '', 1, 0, 1);
INSERT INTO `sys_menus` VALUES (14, 12, '添加', 3, 'sys.admin.add', '', '', '', 'sys/admin', 'POST', '', 2, 0, 1);
INSERT INTO `sys_menus` VALUES (15, 12, '编辑', 3, 'sys.admin.edit', '', '', '', 'sys/admin', 'PATCH', '', 3, 0, 1);
INSERT INTO `sys_menus` VALUES (16, 12, '删除', 3, 'sys.admin.delete', '', '', '', 'sys/admin', 'DELETE', '', 4, 0, 1);
INSERT INTO `sys_menus` VALUES (17, 12, '预览下级', 3, 'sys.admin.view.children', '', '', '', 'sys/admin/view/\\d+', 'GET', '', 5, 0, 1);
INSERT INTO `sys_menus` VALUES (18, 12, '修改密码', 3, 'sys.admin.edit.password', '', '', '', 'sys/admin/password', 'PATCH', '', 6, 0, 1);
INSERT INTO `sys_menus` VALUES (19, 0, '日志管理', 1, '', '', '', '', '', '', 'log', 2, 0, 1);
INSERT INTO `sys_menus` VALUES (20, 19, '登录日志', 1, '', 'log/login', 'log', 'LoginLog', '', '', 'notes', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (21, 20, '列表', 3, '', '', '', '', 'log/login', 'GET', '', 1, 0, 1);
INSERT INTO `sys_menus` VALUES (22, 19, '操作日志', 1, '', 'log/operation', 'log', 'OperationLog', '', '', 'view-list', 2, 1, 1);
INSERT INTO `sys_menus` VALUES (23, 22, '列表', 3, '', '', '', '', 'log/operation', 'GET', '', 1, 0, 1);
INSERT INTO `sys_menus` VALUES (24, 0, '运维管理', 1, '', '', '', '', '', '', 'tool', 3, 0, 1);
INSERT INTO `sys_menus` VALUES (25, 24, '附件管理', 1, '', 'devops/attachment', 'devops', 'Attachment', '', '', 'folder-open', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (26, 25, '列表', 3, '', '', '', '', 'devops/attachment', 'GET', '', 1, 0, 1);
INSERT INTO `sys_menus` VALUES (27, 0, '实验功能', 1, '', '', '', '', '', '', 'experiment', 4, 0, 1);
INSERT INTO `sys_menus` VALUES (28, 27, '富文本编辑器', 1, '', 'test/editor', 'test', 'RichTextEditor', '', '', 'add-text', 1, 1, 1);
INSERT INTO `sys_menus` VALUES (29, 0, '项目仓库', 4, '', 'https://gitee.com/lajixiaohao/wei-admin-plus', '', '', '', '', 'github', 5, 0, 1);

-- ----------------------------
-- Table structure for sys_operation_logs
-- ----------------------------
DROP TABLE IF EXISTS `sys_operation_logs`;
CREATE TABLE `sys_operation_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '操作日志id',
  `admin_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员id',
  `uri` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '资源地址（RESTful API）',
  `method` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '请求方式',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '操作详情',
  `ipv4` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '操作者ipv4',
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '用户代理',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统操作日志表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for sys_roles
-- ----------------------------
DROP TABLE IF EXISTS `sys_roles`;
CREATE TABLE `sys_roles`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '角色id',
  `parent_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级id，0为超管，有且只有一个',
  `role_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色名称',
  `introduce` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '角色简介',
  `menu_ids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '角色菜单权限id',
  `is_able` tinyint UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否启用；1是0否',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统角色表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sys_roles
-- ----------------------------
INSERT INTO `sys_roles` VALUES (1, 0, '超级管理员', '系统预设的超级管理员', NULL, 1, '2023-12-31 07:00:00', '2023-12-31 07:00:00');

SET FOREIGN_KEY_CHECKS = 1;
