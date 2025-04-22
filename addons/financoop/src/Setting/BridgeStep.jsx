import BridgeStep from "../../../../src/components/Templates/Steps/BridgeStep";
import { useTemplateConfig } from "../../../../src/providers/Templates";

const { useMemo, useEffect } = wp.element;

const API_FIELDS = ["campaign_id"];

export default function FinancoopBridgeStep({ fields, data, setData }) {
  const config = useTemplateConfig();

  const campaigns = useMemo(() => data._campaigns || [], [data._campaigns]);

  const campaignOptions = useMemo(() => {
    return campaigns
      .filter((campaign) => {
        return campaign.state === "open" || campaign.state === "draft";
      })
      .filter((campaign) => {
        switch (config.name) {
          case "financoop-subscription-requests":
            return campaign.has_subscription_source;
          case "financoop-loan-requests":
            return campaign.has_loan_source;
          case "financoop-donation-requests":
            return campaign.has_donation_source;
        }
      })
      .map(({ id, name }) => ({
        value: id,
        label: name,
      }));
  }, [campaigns]);

  const standardFields = useMemo(
    () => fields.filter(({ name }) => !API_FIELDS.includes(name)),
    [fields]
  );

  const apiFields = useMemo(() => {
    return fields
      .filter(({ name }) => API_FIELDS.includes(name))
      .map((field) => {
        if (field.name === "campaign_id") {
          return {
            ...field,
            type: "options",
            options: campaignOptions,
          };
        }
      });
  }, [fields, campaignOptions]);

  useEffect(() => {
    const defaults = {};

    if (campaignOptions.length > 0 && !data.campaign_id) {
      defaults.campaign_id = campaignOptions[0].value;
    }

    setData(defaults);
  }, [campaignOptions]);

  return (
    <BridgeStep
      fields={standardFields.concat(apiFields)}
      data={data}
      setData={setData}
    />
  );
}
