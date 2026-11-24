import type { PermissionGroup, RoleData, RolesStrings } from "../../resources/js/types";

export const strings: RolesStrings = {
  roles: "ރޯލްތައް",
  new_role: "އައު ރޯލެއް",
  search: "ރޯލްތައް ހޯދާ",
  name: "ނަން",
  name_help: "އިނގިރޭސި އަކުރު، ނަންބަރު، ޑޭޝް އަދި އަންޑަސްކޯ",
  description: "ތަފްޞީލު",
  permissions: "ހުއްދަތައް",
  permissions_count: ":count ހުއްދަ",
  actions: "ޢަމަލުތައް",
  view: "ބަލާ",
  edit: "ބަދަލުކުރޭ",
  delete: "ފުހެލާ",
  save: "ސޭވްކުރޭ",
  create: "ރޯލް އުފައްދާ",
  back: "ރޯލްތަކަށް އެނބުރި",
  confirm_delete: "މި ރޯލް ފުހެލަންވީތޯ؟",
  no_roles: "އަދި އެއްވެސް ރޯލެއް ނެތް",
  no_roles_message: "ތިބާ އުފައްދާ ރޯލްތައް މިތަނުގައި ފެންނާނެ.",
  no_results: "ހޯދި ގޮތާ ގުޅޭ އެއްވެސް ރޯލެއް ނުފެނުނު.",
  select_all: "ހުރިހާ އެއްޗެއް ޚިޔާރުކުރޭ",
  select_none: "އެއްވެސް އެއްޗެއް ޚިޔާރު ނުކުރާ",
  no_permissions: "މި ރޯލަކަށް އެއްވެސް ހުއްދައެއް ނެތް.",
  details: "މަޢުލޫމާތު",
  guard: "ގާޑު",
};

export const groups: PermissionGroup[] = [
  {
    name: "posts",
    label: "ޕޯސްޓްތައް",
    permissions: [
      { id: 1, name: "view_posts", description: "ޕޯސްޓްތައް ބެލުން", granted: true },
      { id: 2, name: "edit_posts", description: "ޕޯސްޓްތައް ބަދަލުކުރުން", granted: false },
    ],
  },
  {
    name: "roles",
    label: "ރޯލްތައް",
    permissions: [{ id: 3, name: "view_roles", description: "ރޯލްތައް ބެލުން", granted: false }],
  },
];

export function makeRole(overrides: Partial<RoleData> = {}): RoleData {
  return {
    id: 5,
    name: "editor",
    description: "ލިޔުންތައް ބަދަލުކުރާ މީހުން",
    translated_description: "ލިޔުންތައް ބަދަލުކުރާ މީހުން",
    guard_name: "web_admin",
    permissions_count: 1,
    can: { view: true, update: true, delete: true },
    links: { show: "/admin/roles/5", edit: "/admin/roles/5/edit", update: "/admin/roles/5", destroy: "/admin/roles/5" },
    created_at: null,
    updated_at: null,
    ...overrides,
  };
}
