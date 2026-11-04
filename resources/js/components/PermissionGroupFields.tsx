import { Button, Card, Checkbox, Errors } from "@javaabu/forms";
import { useState } from "react";
import type { PermissionGroup, RolesStrings } from "../types";

export interface PermissionGroupFieldsProps {
  groups: PermissionGroup[];
  strings: Pick<RolesStrings, "permissions" | "select_all" | "select_none">;
}

/** A card of permission checkboxes (`permissions[]`) per group, with select all and none */
export function PermissionGroupFields({ groups, strings }: PermissionGroupFieldsProps) {
  const [selected, setSelected] = useState<Set<number>>(
    () => new Set(groups.flatMap((group) => group.permissions.filter((permission) => permission.granted).map((permission) => permission.id))),
  );

  const toggle = (ids: number[], on: boolean) =>
    setSelected((current) => {
      const next = new Set(current);
      ids.forEach((id) => (on ? next.add(id) : next.delete(id)));
      return next;
    });

  return (
    <section className="flex flex-col gap-4" data-permission-groups>
      <h2 className="text-lg font-semibold">{strings.permissions}</h2>

      {groups.map((group) => {
        const ids = group.permissions.map((permission) => permission.id);

        return (
          <div key={group.name || "other"} data-permission-group={group.name}>
            <Card
              header={
                <div className="flex flex-wrap items-center justify-between gap-2">
                  <h3 className="font-semibold">{group.label}</h3>
                  <div className="flex flex-wrap items-center gap-2">
                    <Button color="secondary" size="sm" onClick={() => toggle(ids, true)} data-permission-group-select="all">
                      {strings.select_all}
                    </Button>
                    <Button color="secondary" size="sm" onClick={() => toggle(ids, false)} data-permission-group-select="none">
                      {strings.select_none}
                    </Button>
                  </div>
                </div>
              }
            >
              <div className="grid gap-x-6 sm:grid-cols-2 lg:grid-cols-3">
                {group.permissions.map((permission) => (
                  <div key={permission.id} title={permission.name}>
                    <Checkbox
                      name="permissions[]"
                      id={`permission-${permission.id}`}
                      value={permission.id}
                      label={permission.description}
                      model={false}
                      showErrors={false}
                      checked={selected.has(permission.id)}
                      onCheckedChange={(on) => toggle([permission.id], on)}
                    />
                  </div>
                ))}
              </div>
            </Card>
          </div>
        );
      })}

      <Errors name="permissions" />
    </section>
  );
}
