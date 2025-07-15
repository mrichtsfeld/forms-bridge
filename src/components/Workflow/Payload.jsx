import JsonFinger from "../../lib/JsonFinger";
import {
  applyMappers,
  fieldsToPayload,
  payloadToFields,
} from "../../lib/payload";
import PayloadField from "./PayloadField";

const { useMemo } = wp.element;
const { __experimentalItemGroup: ItemGroup, __experimentalItem: Item } =
  wp.components;

export default function StagePayload({
  fields,
  mappers,
  showMutations = false,
  showDiff = false,
  diff = {},
}) {
  const payloadDiff = useMemo(() => {
    if (!showMutations) return diff;

    const payloadDiff = Object.fromEntries(
      Object.entries(diff).map(([key, set]) => [key, new Set(set)])
    );

    mappers
      .map((m) => m)
      .reverse()
      .forEach((mapper) => {
        const [from] = JsonFinger.parse(mapper.from);
        const [to] = JsonFinger.parse(mapper.to);

        if (payloadDiff.enter.has(from)) {
          payloadDiff.enter.delete(from);
          payloadDiff.enter.add(to);
        } else {
          if (payloadDiff.mutated.has(from)) {
            payloadDiff.mutated.delete(from);
            payloadDiff.mutated.add(to);
          }

          if (payloadDiff.touched.has(from)) {
            payloadDiff.touched.delete(from);
            payloadDiff.touched.add(to);
          }
        }
      });

    return payloadDiff;
  }, [diff, showMutations, mappers]);

  const payloadFields = useMemo(() => {
    const output = payloadToFields(
      applyMappers(fieldsToPayload(fields), mappers)
    );

    if (showDiff) {
      output.forEach((field) => {
        field.enter = payloadDiff.enter.has(field.name);
        field.mutated = payloadDiff.mutated.has(field.name);
        field.touched = payloadDiff.touched.has(field.name);
        field.exit = false;
      });

      Array.from(payloadDiff.exit).forEach((name) => {
        output.push({
          name,
          schema: { type: "null" },
          enter: false,
          mutated: false,
          touched: false,
          exit: true,
        });
      });
    }

    return output;
  }, [fields, mappers, showMutations, showDiff, payloadDiff]);

  return (
    <ItemGroup size="large" isSeparated>
      {payloadFields.map((field, i) => (
        <Item key={field.name + i}>
          <PayloadField {...field} showDiff={showDiff} />
        </Item>
      ))}
    </ItemGroup>
  );
}
