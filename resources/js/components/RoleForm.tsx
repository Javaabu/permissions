import { Card, Form, Submit, Text } from "@javaabu/forms";
import type { ReactNode } from "react";
import type { PermissionGroup, RoleData, RolesStrings, RoleTranslations } from "../types";
import { PermissionGroupFields } from "./PermissionGroupFields";

export interface RoleFormProps {
  /** null on the create page */
  role: RoleData | null;
  action: string;
  permissionGroups: PermissionGroup[];
  translations: RoleTranslations | null;
  strings: Pick<
    RolesStrings,
    "details" | "name" | "name_help" | "description" | "permissions" | "select_all" | "select_none" | "save" | "create"
  >;
  /** Replaces the submit button */
  actions?: ReactNode;
}

/** The role's name, description (one per locale when translated) and permissions */
export function RoleForm({ role, action, permissionGroups, translations, strings, actions }: RoleFormProps) {
  const model = role ? { ...role, ...(translations?.values ?? {}) } : (translations?.values ?? undefined);

  return (
    <Form method={role ? "PUT" : "POST"} action={action} model={model} summary className="flex flex-col gap-6" data-role-form>
      <Card title={strings.details}>
        <div className="grid gap-4 md:grid-cols-2">
          <Text name="name" label={strings.name} required maxLength={255} dir="ltr" autoComplete="off" help={strings.name_help} />

          {translations ? (
            <div className="grid gap-4" data-translatable-field="description">
              {translations.inputs.description.map((input) => (
                <Text
                  key={input.name}
                  name={input.name}
                  label={input.label}
                  required={input.primary}
                  maxLength={255}
                  dir={input.direction}
                  lang={input.locale}
                  thaana={input.thaana}
                />
              ))}
            </div>
          ) : (
            <Text name="description" label={strings.description} required maxLength={255} dir="auto" />
          )}
        </div>
      </Card>

      <PermissionGroupFields groups={permissionGroups} strings={strings} />

      <div className="flex flex-wrap items-center gap-2">
        {actions ?? <Submit>{role ? strings.save : strings.create}</Submit>}
      </div>
    </Form>
  );
}
