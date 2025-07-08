import useGeneral from "./useGeneral";

export default function useBackends() {
  const [general, save] = useGeneral();
  return [general.backends || [], (backends) => save({ ...general, backends })];
}
