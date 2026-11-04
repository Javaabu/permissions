import { fireEvent, render, screen } from "@testing-library/react";
import { afterEach, describe, expect, it, vi } from "vitest";
import { PermissionGroupFields } from "../../resources/js/components/PermissionGroupFields";
import { RoleActions } from "../../resources/js/components/RoleActions";
import { RoleForm } from "../../resources/js/components/RoleForm";
import { groups, makeRole, strings } from "./fixtures";

/** Ids of the ticked permission checkboxes (native inputs without an adapter) */
function checked(): string[] {
  return Array.from(document.querySelectorAll<HTMLInputElement>('input[type="checkbox"][name="permissions[]"]'))
    .filter((box) => box.checked)
    .map((box) => box.id);
}

describe("PermissionGroupFields", () => {
  it("starts with the granted permissions and selects a group at once", () => {
    render(<PermissionGroupFields groups={groups} strings={strings} />);

    expect(screen.getByText("ޕޯސްޓްތައް")).toBeTruthy();
    expect(checked()).toEqual(["permission-1"]);

    fireEvent.click(screen.getAllByText(strings.select_all)[0]!);
    expect(checked()).toEqual(["permission-1", "permission-2"]);

    fireEvent.click(screen.getAllByText(strings.select_none)[0]!);
    expect(checked()).toEqual([]);
  });
});

describe("RoleForm", () => {
  it("renders one description field per locale when descriptions are translated", () => {
    render(
      <RoleForm
        role={makeRole()}
        action="/admin/roles/5"
        permissionGroups={groups}
        strings={strings}
        translations={{
          values: { description_en: "Editors", description_dv: "ލިޔުންތައް ބަދަލުކުރާ މީހުން" },
          inputs: {
            description: [
              { field: "description", name: "description_en", locale: "en", direction: "ltr", thaana: false, primary: true, label: "ތަފްޞީލު (English)", language: {} },
              { field: "description", name: "description_dv", locale: "dv", direction: "rtl", thaana: true, primary: false, label: "ތަފްޞީލު (ދިވެހި)", language: {} },
            ],
          },
        }}
      />,
    );

    const dhivehi = document.querySelector<HTMLInputElement>('input[name="description_dv"]')!;

    expect(dhivehi.value).toBe("ލިޔުންތައް ބަދަލުކުރާ މީހުން");
    expect(dhivehi.getAttribute("dir")).toBe("rtl");
    expect(document.querySelector<HTMLInputElement>('input[name="description_en"]')!.value).toBe("Editors");
    expect(document.querySelector('input[name="description"]')).toBeNull();
    expect(document.querySelector<HTMLInputElement>('input[name="_method"]')!.value).toBe("PUT");
    expect(screen.getByText(strings.save)).toBeTruthy();
  });

  it("renders a plain description field otherwise", () => {
    render(<RoleForm role={null} action="/admin/roles" permissionGroups={groups} strings={strings} translations={null} />);

    expect(document.querySelector('input[name="description"]')).toBeTruthy();
    expect(screen.getByText(strings.create)).toBeTruthy();
  });
});

describe("RoleActions", () => {
  afterEach(() => vi.restoreAllMocks());

  it("shows the buttons the user may use and confirms deleting", () => {
    const confirm = vi.spyOn(window, "confirm").mockReturnValue(false);

    render(<RoleActions role={makeRole({ can: { view: true, update: false, delete: true } })} strings={strings} />);

    expect(screen.queryByText(strings.edit)).toBeNull();

    fireEvent.click(screen.getByText(strings.delete));
    expect(confirm).toHaveBeenCalledWith(strings.confirm_delete);
  });
});
