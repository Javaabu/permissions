/** A permission as PermissionResource serializes it */
export interface PermissionData {
  id: number;
  name: string;
  description: string | null;
  translated_description: string;
  group: string | null;
  guard_name: string;
}

/** A role as RoleResource serializes it */
export interface RoleData {
  id: number;
  name: string;
  description: string | null;
  translated_description: string;
  guard_name: string;
  /** Every locale's description, when javaabu/translatable translates descriptions */
  translations?: Record<string, string | null>;
  permissions_count?: number;
  permissions?: PermissionData[];
  can: { view: boolean; update: boolean; delete: boolean };
  links?: { show: string; edit: string; update: string; destroy: string };
  created_at: string | null;
  updated_at: string | null;
}

/** One permission on the role form */
export interface PermissionOption {
  id: number;
  name: string;
  /** Translated */
  description: string;
  granted: boolean;
}

/** Permissions of one registered group, in registration order */
export interface PermissionGroup {
  name: string;
  /** Translated */
  label: string;
  permissions: PermissionOption[];
}

/** One description input per locale, from javaabu/translatable */
export interface LocaleInputData {
  field: string;
  /** e.g. `description_dv` */
  name: string;
  locale: string;
  direction: "ltr" | "rtl";
  thaana: boolean;
  primary: boolean;
  /** "Description (ދިވެހި)" */
  label: string;
  language: Record<string, unknown>;
}

export interface RoleTranslations {
  values: Record<string, string | null>;
  inputs: { description: LocaleInputData[] };
}

export interface RoleRoutes {
  index: string;
  create: string;
  store: string;
  show: string;
  edit: string;
  update: string;
  destroy: string;
}

export interface RolesStrings {
  roles: string;
  new_role: string;
  search: string;
  name: string;
  name_help: string;
  description: string;
  permissions: string;
  permissions_count: string;
  actions: string;
  view: string;
  edit: string;
  delete: string;
  save: string;
  create: string;
  back: string;
  confirm_delete: string;
  no_roles: string;
  no_roles_message: string;
  no_results: string;
  select_all: string;
  select_none: string;
  no_permissions: string;
  details: string;
  guard: string;
}

export interface Paginated<T> {
  data: T[];
  links: Record<string, string | null>;
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    path: string;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
}

/** Props every page gets */
export interface RolesPageProps {
  title: string;
  guard: string;
  /** Route names, e.g. `routes.edit` is `admin.roles.edit` */
  routes: RoleRoutes;
  urls: { index: string; create: string; store: string; show?: string; edit?: string; update?: string; destroy?: string };
  can: { create: boolean; update: boolean; delete: boolean };
  strings: RolesStrings;
}

export interface RolesIndexProps extends RolesPageProps {
  roles: Paginated<RoleData>;
  search: string | null;
}

export interface RoleFormProps extends RolesPageProps {
  role: RoleData | null;
  permission_groups: PermissionGroup[];
  translations: RoleTranslations | null;
}

export interface RolesShowProps extends RolesPageProps {
  role: RoleData;
  permission_groups: PermissionGroup[];
}
