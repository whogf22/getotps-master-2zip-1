import { useRef, useMemo, Suspense } from "react";
import { Canvas, useFrame } from "@react-three/fiber";
import { Stars } from "@react-three/drei";
import * as THREE from "three";
import { SceneErrorBoundary } from "./SceneErrorBoundary";

function NetworkGlobe() {
  const globeRef = useRef<THREE.Group>(null);

  useFrame((state) => {
    if (globeRef.current) globeRef.current.rotation.y = state.clock.elapsedTime * 0.06;
  });

  return (
    <group ref={globeRef} position={[1.5, 0.3, -2]}>
      <mesh>
        <sphereGeometry args={[2.4, 48, 48]} />
        <meshPhysicalMaterial
          color="#030a18"
          emissive="#051525"
          emissiveIntensity={0.8}
          metalness={0.6}
          roughness={0.4}
          clearcoat={0.3}
          clearcoatRoughness={0.2}
        />
      </mesh>
      <mesh>
        <sphereGeometry args={[2.42, 32, 32]} />
        <meshBasicMaterial color="#0ea5e9" wireframe transparent opacity={0.06} />
      </mesh>
      <mesh>
        <sphereGeometry args={[2.44, 20, 20]} />
        <meshBasicMaterial color="#22d3ee" wireframe transparent opacity={0.035} />
      </mesh>
      <mesh>
        <sphereGeometry args={[2.65, 32, 32]} />
        <meshBasicMaterial color="#0ea5e9" transparent opacity={0.025} side={THREE.BackSide} />
      </mesh>
    </group>
  );
}

function GlassSIMCard() {
  const sim = useRef<THREE.Group>(null);

  useFrame((state) => {
    if (sim.current) {
      sim.current.rotation.y = Math.sin(state.clock.elapsedTime * 0.4) * 0.3 + 0.2;
      sim.current.rotation.x = Math.sin(state.clock.elapsedTime * 0.3) * 0.1;
      sim.current.position.y = -0.8 + Math.sin(state.clock.elapsedTime * 0.5) * 0.15;
    }
  });

  return (
    <group ref={sim} position={[-3.2, -0.8, 1]}>
      <mesh>
        <boxGeometry args={[0.8, 1.1, 0.04]} />
        <meshPhysicalMaterial
          color="#0d2847"
          emissive="#0ea5e9"
          emissiveIntensity={0.15}
          metalness={0.9}
          roughness={0.1}
          clearcoat={1}
          clearcoatRoughness={0.05}
          transparent
          opacity={0.85}
        />
      </mesh>
      <mesh position={[0.08, -0.15, 0.025]}>
        <boxGeometry args={[0.35, 0.3, 0.008]} />
        <meshStandardMaterial color="#c49a2a" emissive="#c49a2a" emissiveIntensity={0.3} metalness={1} roughness={0.15} />
      </mesh>
      {[[-0.02, 0, 0], [0.1, 0, 0], [0.22, 0, 0], [-0.02, -0.08, 0], [0.1, -0.08, 0], [0.22, -0.08, 0]].map(([x, y, z], i) => (
        <mesh key={i} position={[x, -0.08 + y!, 0.03]}>
          <boxGeometry args={[0.09, 0.06, 0.002]} />
          <meshStandardMaterial color="#d4a833" metalness={1} roughness={0.2} />
        </mesh>
      ))}
      <mesh position={[-0.2, 0.35, 0.025]}>
        <boxGeometry args={[0.15, 0.08, 0.005]} />
        <meshStandardMaterial color="#22d3ee" emissive="#22d3ee" emissiveIntensity={0.8} />
      </mesh>
    </group>
  );
}

function GlassPanel({ position, rotation, width, height, label }: { position: [number, number, number]; rotation?: [number, number, number]; width: number; height: number; label?: string }) {
  const panel = useRef<THREE.Mesh>(null);

  useFrame((state) => {
    if (panel.current) {
      panel.current.position.y = position[1] + Math.sin(state.clock.elapsedTime * 0.5 + position[0]) * 0.08;
    }
  });

  return (
    <group>
      <mesh ref={panel} position={position} rotation={rotation || [0, 0, 0]}>
        <boxGeometry args={[width, height, 0.02]} />
        <meshPhysicalMaterial
          color="#0a1e38"
          emissive="#0a2540"
          emissiveIntensity={0.3}
          metalness={0.2}
          roughness={0.1}
          transparent
          opacity={0.35}
          clearcoat={0.8}
          clearcoatRoughness={0.1}
        />
      </mesh>
      <mesh position={[position[0], position[1] + height / 2 - 0.05, position[2] + 0.015]} rotation={rotation || [0, 0, 0]}>
        <boxGeometry args={[width - 0.06, 0.02, 0.005]} />
        <meshBasicMaterial color="#22d3ee" transparent opacity={0.5} />
      </mesh>
      <mesh position={[position[0] - width / 2 + 0.06, position[1] + height / 2 - 0.05, position[2] + 0.015]} rotation={rotation || [0, 0, 0]}>
        <sphereGeometry args={[0.025, 8, 8]} />
        <meshBasicMaterial color="#22c55e" />
      </mesh>
    </group>
  );
}

function NetworkLines() {
  const group = useRef<THREE.Group>(null);

  const lines = useMemo(() => {
    const l = [];
    for (let i = 0; i < 12; i++) {
      const a1 = (i / 12) * Math.PI * 2;
      const a2 = ((i + 3 + Math.floor(Math.random() * 4)) % 12 / 12) * Math.PI * 2;
      const r = 2.5;
      const from = new THREE.Vector3(Math.cos(a1) * r + 1.5, Math.sin(a1) * r * 0.6 + 0.3, -2 + Math.sin(a1) * 0.5);
      const to = new THREE.Vector3(Math.cos(a2) * r + 1.5, Math.sin(a2) * r * 0.6 + 0.3, -2 + Math.sin(a2) * 0.5);
      const mid = new THREE.Vector3().lerpVectors(from, to, 0.5);
      mid.z += 1.5 + Math.random();
      const pts = [];
      for (let j = 0; j <= 20; j++) {
        const t = j / 20;
        const p = new THREE.Vector3();
        p.lerpVectors(from, to, t);
        p.z += Math.sin(t * Math.PI) * (1.5 + Math.random() * 0.5);
        pts.push(p);
      }
      l.push(pts);
    }
    return l;
  }, []);

  useFrame((state) => {
    if (group.current) {
      group.current.children.forEach((child, i) => {
        (child as THREE.Line).material = new THREE.LineBasicMaterial({
          color: i % 3 === 0 ? "#22d3ee" : i % 3 === 1 ? "#818cf8" : "#34d399",
          transparent: true,
          opacity: 0.04 + Math.sin(state.clock.elapsedTime * 0.8 + i) * 0.03,
        });
      });
    }
  });

  return (
    <group ref={group}>
      {lines.map((pts, i) => {
        const geo = new THREE.BufferGeometry().setFromPoints(pts);
        return (
          <primitive
            key={i}
            object={new THREE.Line(geo, new THREE.LineBasicMaterial({ color: "#22d3ee", transparent: true, opacity: 0.05 }))}
          />
        );
      })}
    </group>
  );
}

function OTPPulse({ position, delay, color }: { position: [number, number, number]; delay: number; color: string }) {
  const mesh = useRef<THREE.Mesh>(null);

  useFrame((state) => {
    if (mesh.current) {
      const t = ((state.clock.elapsedTime + delay) % 3) / 3;
      const scale = 0.1 + t * 0.4;
      mesh.current.scale.set(scale, scale, scale);
      (mesh.current.material as THREE.MeshBasicMaterial).opacity = (1 - t) * 0.6;
    }
  });

  return (
    <mesh ref={mesh} position={position}>
      <sphereGeometry args={[1, 16, 16]} />
      <meshBasicMaterial color={color} transparent opacity={0.3} />
    </mesh>
  );
}

function SurfaceDots() {
  const mesh = useRef<THREE.Points>(null);
  const count = 180;
  const positions = useMemo(() => {
    const pos = new Float32Array(count * 3);
    for (let i = 0; i < count; i++) {
      const theta = Math.random() * Math.PI * 2;
      const phi = Math.acos(2 * Math.random() - 1);
      const r = 2.48;
      pos[i * 3] = r * Math.sin(phi) * Math.cos(theta) + 1.5;
      pos[i * 3 + 1] = r * Math.sin(phi) * Math.sin(theta) + 0.3;
      pos[i * 3 + 2] = r * Math.cos(phi) - 2;
    }
    return pos;
  }, []);

  useFrame((state) => {
    if (mesh.current) {
      (mesh.current.material as THREE.PointsMaterial).opacity = 0.4 + Math.sin(state.clock.elapsedTime) * 0.2;
    }
  });

  return (
    <points ref={mesh}>
      <bufferGeometry>
        <bufferAttribute attach="attributes-position" array={positions} count={count} itemSize={3} />
      </bufferGeometry>
      <pointsMaterial size={0.035} color="#22d3ee" transparent opacity={0.5} sizeAttenuation />
    </points>
  );
}

function BackgroundDust() {
  const mesh = useRef<THREE.Points>(null);
  const count = 800;
  const positions = useMemo(() => {
    const pos = new Float32Array(count * 3);
    for (let i = 0; i < count; i++) {
      pos[i * 3] = (Math.random() - 0.5) * 30;
      pos[i * 3 + 1] = (Math.random() - 0.5) * 20;
      pos[i * 3 + 2] = (Math.random() - 0.5) * 15 - 5;
    }
    return pos;
  }, []);

  useFrame((state) => {
    if (mesh.current) mesh.current.rotation.y = state.clock.elapsedTime * 0.008;
  });

  return (
    <points ref={mesh}>
      <bufferGeometry>
        <bufferAttribute attach="attributes-position" array={positions} count={count} itemSize={3} />
      </bufferGeometry>
      <pointsMaterial size={0.012} color="#334155" transparent opacity={0.5} sizeAttenuation />
    </points>
  );
}

function OrbitRings() {
  const r1 = useRef<THREE.Mesh>(null);
  const r2 = useRef<THREE.Mesh>(null);

  useFrame((state) => {
    if (r1.current) {
      r1.current.rotation.z = state.clock.elapsedTime * 0.12;
      r1.current.rotation.x = 0.6;
    }
    if (r2.current) {
      r2.current.rotation.z = -state.clock.elapsedTime * 0.08;
      r2.current.rotation.x = 1.1;
    }
  });

  return (
    <group position={[1.5, 0.3, -2]}>
      <mesh ref={r1}>
        <torusGeometry args={[3.2, 0.006, 4, 120]} />
        <meshBasicMaterial color="#0ea5e9" transparent opacity={0.15} />
      </mesh>
      <mesh ref={r2}>
        <torusGeometry args={[3.8, 0.004, 4, 120]} />
        <meshBasicMaterial color="#818cf8" transparent opacity={0.08} />
      </mesh>
    </group>
  );
}

function CameraRig() {
  useFrame((state) => {
    state.camera.position.x += (state.mouse.x * 0.8 - state.camera.position.x) * 0.02;
    state.camera.position.y += (state.mouse.y * 0.5 + 0.5 - state.camera.position.y) * 0.02;
    state.camera.lookAt(0.5, 0, -1);
  });
  return null;
}

function Scene() {
  return (
    <>
      <color attach="background" args={["#030810"]} />

      <ambientLight intensity={0.08} />
      <pointLight position={[6, 4, 3]} intensity={4} color="#22d3ee" distance={20} />
      <pointLight position={[-4, 3, -2]} intensity={2.5} color="#818cf8" distance={18} />
      <pointLight position={[0, -4, 4]} intensity={1.5} color="#0ea5e9" distance={15} />
      <pointLight position={[3, 0, 6]} intensity={2} color="#22d3ee" distance={12} />
      <spotLight position={[0, 8, 0]} angle={0.4} penumbra={0.8} intensity={1.5} color="#0ea5e9" />

      <Stars radius={60} depth={40} count={2000} factor={2} fade speed={0.3} />

      <NetworkGlobe />
      <SurfaceDots />
      <OrbitRings />
      <NetworkLines />
      <GlassSIMCard />
      <BackgroundDust />

      <GlassPanel position={[-3.5, 1.8, -0.5]} width={1.4} height={0.9} rotation={[0, 0.25, 0]} />
      <GlassPanel position={[4.2, 1.5, -1]} width={1.2} height={0.7} rotation={[0, -0.2, 0]} />
      <GlassPanel position={[-2.5, -1.8, 0.5]} width={1.0} height={0.6} rotation={[0, 0.15, 0.05]} />

      <OTPPulse position={[2.5, 1.5, -1]} delay={0} color="#22d3ee" />
      <OTPPulse position={[-1.5, 0.8, -1.5]} delay={1} color="#818cf8" />
      <OTPPulse position={[0.5, -1.2, -1]} delay={2} color="#34d399" />

      <CameraRig />
    </>
  );
}

export function HeroScene() {
  return (
    <div className="absolute inset-0 w-full h-full">
      <SceneErrorBoundary fallback={<div className="absolute inset-0 hero-fallback-bg" />}>
        <Canvas
          camera={{ position: [0, 0.5, 9], fov: 48 }}
          gl={{ antialias: true, alpha: false, powerPreference: "high-performance" }}
          dpr={[1, 1.5]}
        >
          <Suspense fallback={null}>
            <Scene />
          </Suspense>
        </Canvas>
      </SceneErrorBoundary>
    </div>
  );
}
