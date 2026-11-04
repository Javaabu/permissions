import { afterEach, describe, expect, it, vi } from "vitest";
import { selectGroup } from "../../resources/js/roles";

function groupMarkup(): string {
  return `
    <form data-confirm="މި ރޯލް ފުހެލަންވީތޯ؟"><button type="submit">ފުހެލާ</button></form>
    <div data-permission-group="posts">
      <button type="button" data-permission-group-select="all">all</button>
      <button type="button" data-permission-group-select="none">none</button>
      <input type="checkbox" name="permissions[]" value="1">
      <input type="checkbox" name="permissions[]" value="2" checked>
      <input type="checkbox" name="permissions[]" value="3" disabled>
    </div>
    <div data-permission-group="roles">
      <input type="checkbox" name="permissions[]" value="4">
    </div>`;
}

function boxes(): HTMLInputElement[] {
  return Array.from(document.querySelectorAll<HTMLInputElement>('input[name="permissions[]"]'));
}

describe("Blade role script", () => {
  afterEach(() => {
    vi.restoreAllMocks();
    document.body.innerHTML = "";
  });

  it("ticks and unticks every enabled permission of one group", () => {
    document.body.innerHTML = groupMarkup();

    selectGroup(document.querySelector("[data-permission-group=posts]")!, true);
    expect(boxes().map((box) => box.checked)).toEqual([true, true, false, false]);

    selectGroup(document.querySelector("[data-permission-group=posts]")!, false);
    expect(boxes().map((box) => box.checked)).toEqual([false, false, false, false]);
  });

  it("selects a group from its buttons", () => {
    document.body.innerHTML = groupMarkup();

    document.querySelector<HTMLElement>('[data-permission-group-select="all"]')!.click();
    expect(boxes().map((box) => box.checked)).toEqual([true, true, false, false]);

    document.querySelector<HTMLElement>('[data-permission-group-select="none"]')!.click();
    expect(boxes().map((box) => box.checked)).toEqual([false, false, false, false]);
  });

  it("asks before submitting a form with data-confirm", () => {
    document.body.innerHTML = groupMarkup();
    const confirm = vi.spyOn(window, "confirm").mockReturnValue(false);
    const form = document.querySelector("form")!;
    const event = new Event("submit", { bubbles: true, cancelable: true });

    form.dispatchEvent(event);

    expect(confirm).toHaveBeenCalledWith("މި ރޯލް ފުހެލަންވީތޯ؟");
    expect(event.defaultPrevented).toBe(true);
  });
});
