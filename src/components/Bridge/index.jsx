// source
// import { useForms } from "../../providers/Forms";
// import useGeneral from "../../hooks/useGeneral";
import useBridgeNames from "../../hooks/useBridgeNames";
import CustomFields from "../CustomFields";
import Workflow from "../Workflow";
import NewBridge from "./NewBridge";
import RemoveButton from "../RemoveButton";
import { downloadJson } from "../../lib/utils";
import BridgeFields, { INTERNALS } from "./Fields";

const { ToggleControl, Button, __experimentalSpacer: Spacer } = wp.components;
const { useState, useEffect, useMemo, useCallback } = wp.element;
const { __ } = wp.i18n;

export default function Bridge({ data, update, remove, schema }) {
  const [state, setState] = useState({ ...data });

  const bridgeNames = useBridgeNames();

  const nameConflict = useMemo(() => {
    if (!state.name) return false;
    return (
      state.name.trim() !== data.name && bridgeNames.has(state.name.trim())
    );
  }, [bridgeNames, state.name]);

  const validate = useCallback(
    (data) => {
      return !!Object.keys(schema.properties)
        .filter((prop) => !INTERNALS.includes(prop))
        .reduce((isValid, prop) => {
          const value = data[prop];

          if (schema.properties[prop].pattern) {
            isValid = new RegExp(schema.properties[prop].pattern).test(value);
          }

          return isValid && value;
        }, true);
    },
    [schema]
  );

  const isValid = useMemo(
    () => validate(state) && !nameConflict,
    [state, nameConflict]
  );

  useEffect(() => {
    if (isValid) update(state);
  }, [isValid, state]);

  useEffect(() => {
    setState(data);
  }, [data.name]);

  function exportConfig() {
    const bridgeData = { ...data };
    delete bridgeData.is_valid;

    downloadJson(data, data.name + " bridge config");
  }

  return (
    <div
      style={{
        padding: "calc(24px) calc(32px)",
        width: "calc(100% - 64px)",
        backgroundColor: "rgb(245, 245, 245)",
      }}
    >
      <div
        style={{
          display: "flex",
          gap: "1em",
          flexWrap: "wrap",
        }}
      >
        <BridgeFields
          data={state}
          setData={setState}
          schema={schema}
          errors={{
            name: nameConflict
              ? __("This name is already in use", "forms-bridge")
              : false,
          }}
        />
      </div>
      <Spacer paddingY="calc(8px)" />
      <div
        style={{
          display: "flex",
          flexWrap: "wrap",
          justifyContent: "space-between",
        }}
      >
        <div style={{ display: "flex", gap: "1em" }}>
          <CustomFields
            customFields={state.custom_fields || []}
            setCustomFields={(custom_fields) =>
              setState({
                ...state,
                custom_fields,
              })
            }
          />
          <Workflow
            backend={state.backend}
            formId={state.form_id}
            customFields={state.custom_fields}
            mutations={state.mutations}
            workflow={state.workflow}
            setWorkflow={(workflow) => setState({ ...state, workflow })}
            setMutationMappers={(mutation, mappers) => {
              setState({
                ...state,
                mutations: state.mutations
                  .slice(0, mutation)
                  .concat([mappers])
                  .concat(state.mutations.slice(mutation + 1)),
              });
            }}
          />
        </div>
        <div style={{ display: "flex", gap: "1em" }}>
          <div style={{ display: "flex", alignItems: "center" }}>
            <ToggleControl
              disabled={!isValid}
              checked={state.enabled && isValid}
              onChange={() => setState({ ...state, enabled: !state.enabled })}
              label={__("Active", "forms-bridge")}
              __nextHasNoMarginBottom
            />
          </div>
          <Button
            size="compact"
            variant="tertiary"
            style={{
              height: "40px",
              width: "40px",
              justifyContent: "center",
              fontSize: "1.5em",
              border: "1px solid",
              color: "gray",
            }}
            onClick={exportConfig}
            __next40pxDefaultSize
            label={__("Download bridge config", "forms-bridge")}
            showTooltip
          >
            ⬇
          </Button>
          <RemoveButton
            onClick={() => remove(data)}
            size="compact"
            style={{
              width: "40px",
              height: "40px",
              fontWeight: "bold",
              fontSize: "1.5em",
            }}
          >
            <svg
              style={{ width: "12px" }}
              width="45.811565mm"
              height="67.009642mm"
              viewBox="0 0 45.811565 67.009642"
            >
              <g id="layer1" transform="translate(-40,-65.355415)">
                <rect
                  style={{
                    fill: "#ffffff",
                    strokeWidth: 4.99998,
                    strokeLinecap: "round",
                    strokeLinejoin: "round",
                    strokeMiterlimit: 9,
                  }}
                  id="rect234"
                  width="45.811565"
                  height="8.6819019"
                  x="40"
                  y="65.355415"
                />
                <path
                  id="rect473"
                  style={{
                    fill: "#ffffff",
                    strokeWidth: 4.99998,
                    strokeLinecap: "round",
                    strokeLinejoin: "round",
                    strokeMiterlimit: 9,
                  }}
                  d="m 40.500274,75.758567 3.276235,51.199693 h 0.01306 c 0.252952,3.00397 2.522367,5.4068 5.552375,5.4068 h 26.954544 c 3.030011,0 5.299425,-2.40283 5.552376,-5.4068 h 0.01305 L 85.138155,75.758567 H 79.026688 46.611752 Z"
                />
              </g>
            </svg>
          </RemoveButton>
        </div>
      </div>
    </div>
  );
}

Bridge.NewBridge = NewBridge;
